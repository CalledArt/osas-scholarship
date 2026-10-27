<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records which preset a scholarship was posted from, so only one posting
     * of each preset is open at a time and a student holds at most one of
     * them. Postings made before this column existed are matched once: the
     * DOST preset is the only one that set stem_courses_only, and the other
     * presets by the exact title they fill in. The titles are copied here
     * rather than read from ScholarshipPresets, so a later rename there
     * cannot change what this migration did. Anything else stays unset.
     */
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->string('preset')->nullable();
        });

        DB::table('scholarships')->where('stem_courses_only', true)->update(['preset' => 'dost']);
        DB::table('scholarships')->whereNull('preset')->where('title', 'Tulong Dunong Program (TDP)')->update(['preset' => 'tdp']);
        DB::table('scholarships')->whereNull('preset')->where('title', 'Tertiary Education Subsidy (TES)')->update(['preset' => 'tes']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn('preset');
        });
    }
};
