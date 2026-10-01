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
        Schema::table('scholarship_required_documents', function (Blueprint $table) {
            $table->string('sample_disk')->nullable()->after('description');
            $table->string('sample_path')->nullable()->after('sample_disk');
            $table->string('sample_original_name')->nullable()->after('sample_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_required_documents', function (Blueprint $table) {
            $table->dropColumn(['sample_disk', 'sample_path', 'sample_original_name']);
        });
    }
};
