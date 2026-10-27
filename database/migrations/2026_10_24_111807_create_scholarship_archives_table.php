<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * When OSAS deletes a scholarship whose fund allocation was completed (a
     * fund total entered and at least one student passed), a copy of it and
     * of every recipient's line on the fund allocation sheet is kept here.
     * Everything is copied rather than referenced, since the scholarship and
     * its applications are gone; the user links only survive while those
     * accounts do. An archive is written as the scholarship is deleted, so
     * its created_at is when that happened.
     */
    public function up(): void
    {
        Schema::create('scholarship_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scholarship_id')->comment('The deleted scholarship\'s id; no foreign key, since the row is gone.');
            $table->string('title');
            $table->text('description');
            $table->string('type');
            $table->string('preset')->nullable();
            $table->boolean('stem_courses_only')->default(false);
            $table->unsignedInteger('slots');
            $table->date('deadline')->nullable();
            $table->string('status');
            $table->decimal('total_funds', 12, 2);
            $table->decimal('share_amount', 12, 2);
            $table->unsignedInteger('recipient_count');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('scholarship_archive_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_archive_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('application_reference');
            $table->string('student_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name');
            $table->string('course')->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_archive_recipients');
        Schema::dropIfExists('scholarship_archives');
    }
};
