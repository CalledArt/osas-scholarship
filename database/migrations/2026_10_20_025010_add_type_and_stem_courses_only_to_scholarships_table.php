<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Existing postings are taken to be government scholarships, which every
     * one posted so far is; OSAS changes any that aren't on the Edit page.
     * None of them was limited to STEM courses.
     */
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->string('type')->default('government');
            $table->boolean('stem_courses_only')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn(['type', 'stem_courses_only']);
        });
    }
};
