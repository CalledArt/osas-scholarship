<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Marks a rejection that came from a student being on the pass list while
     * on leave of absence, since a student on leave can't take a scholarship.
     * Their page then says they passed instead of "not on the pass list". Every
     * earlier rejection was an ordinary one, so the default needs no backfill.
     */
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->boolean('passed_while_on_leave')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn('passed_while_on_leave');
        });
    }
};
