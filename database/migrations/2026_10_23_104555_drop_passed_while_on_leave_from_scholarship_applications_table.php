<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Putting a student on leave of absence now voids their applications, so
     * the pass list never meets one on leave and has no rejection to flag.
     */
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn('passed_while_on_leave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->boolean('passed_while_on_leave')->default(false);
        });
    }
};
