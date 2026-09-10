<?php

use App\Support\PersonName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
        });

        $this->backfillNameParts();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }

    /**
     * Seed the new columns from the full name each existing row already has.
     *
     * One UPDATE per row: the split differs per row, so there is nothing to
     * batch, and this runs once by hand rather than on the request path where
     * the round-trip cost would matter.
     */
    private function backfillNameParts(): void
    {
        DB::table('users')
            ->select('id', 'name')
            ->orderBy('id')
            ->chunkById(500, function (Collection $users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(PersonName::split($user->name));
                }
            });
    }
};
