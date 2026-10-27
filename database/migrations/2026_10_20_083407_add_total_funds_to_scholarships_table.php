<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The fund is split equally among the students who pass. It stays empty
     * until OSAS knows the amount, so existing postings need no backfill.
     */
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->decimal('total_funds', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn('total_funds');
        });
    }
};
