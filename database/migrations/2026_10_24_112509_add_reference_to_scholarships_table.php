<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Gives every scholarship a reference number, "SCH-" then the year it was
     * posted and its id to five digits (SCH-2026-00012), and keeps it on the
     * archive of a deleted one. Postings and archives made before this get
     * theirs here, from the same year and id. The format is copied here rather
     * than read from Scholarship, so a later change there cannot change what
     * this migration did.
     */
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->string('reference')->nullable()->unique();
        });

        Schema::table('scholarship_archives', function (Blueprint $table) {
            $table->string('scholarship_reference')->nullable();
        });

        $reference = fn (int $id, ?string $postedAt): string => sprintf('SCH-%d-%05d', Carbon::parse($postedAt ?? 'now')->year, $id);

        DB::table('scholarships')->whereNull('reference')->orderBy('id')->get(['id', 'created_at'])
            ->each(fn (object $scholarship) => DB::table('scholarships')->where('id', $scholarship->id)
                ->update(['reference' => $reference($scholarship->id, $scholarship->created_at)]));

        DB::table('scholarship_archives')->whereNull('scholarship_reference')->orderBy('id')->get(['id', 'scholarship_id', 'posted_at'])
            ->each(fn (object $archive) => DB::table('scholarship_archives')->where('id', $archive->id)
                ->update(['scholarship_reference' => $reference($archive->scholarship_id, $archive->posted_at)]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_archives', function (Blueprint $table) {
            $table->dropColumn('scholarship_reference');
        });

        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn('reference');
        });
    }
};
