<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('role');
            $table->string('student_number')->nullable()->unique()->after('date_of_birth');
            $table->string('course')->nullable()->after('student_number');
            $table->unsignedTinyInteger('year_level')->nullable()->after('course');
            $table->string('contact_number')->nullable()->after('year_level');
            $table->string('address')->nullable()->after('contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'student_number',
                'course',
                'year_level',
                'contact_number',
                'address',
            ]);
        });
    }
};
