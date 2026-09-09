<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Postgres does not automatically index foreign-key columns (unlike
     * MySQL), so every one added by earlier migrations needs an explicit
     * index here to avoid sequential scans as these tables grow.
     */
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->index('created_by');
        });

        Schema::table('scholarship_required_documents', function (Blueprint $table) {
            $table->index('scholarship_id');
        });

        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->index(['user_id', 'submitted_at']);
        });

        Schema::table('scholarship_application_documents', function (Blueprint $table) {
            $table->index('scholarship_required_document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
        });

        Schema::table('scholarship_required_documents', function (Blueprint $table) {
            $table->dropIndex(['scholarship_id']);
        });

        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'submitted_at']);
        });

        Schema::table('scholarship_application_documents', function (Blueprint $table) {
            $table->dropIndex(['scholarship_required_document_id']);
        });
    }
};
