<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records when a passed student received their share of the fund, how
     * much it was, and which OSAS staff member marked it. The amount is kept
     * as released rather than recomputed, since the share changes if more
     * students pass or the fund is edited afterwards.
     */
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->decimal('funds_released_amount', 12, 2)->nullable();
            $table->timestamp('funds_released_at')->nullable();
            $table->foreignId('funds_released_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('funds_released_by');
            $table->dropColumn(['funds_released_amount', 'funds_released_at']);
        });
    }
};
