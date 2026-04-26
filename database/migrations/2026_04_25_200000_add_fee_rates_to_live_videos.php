<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auction fee fractions (0–1) live on live_videos only. Item-level rates were removed; see 2026_04_26_100000.
     */
    public function up(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->integer('tax_amount')->nullable();
            $table->integer('commission_amount')->nullable();
            $table->enum('commission_payer', ['buyer', 'seller'])->default('buyer');
            $table->integer('service_fee')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->dropColumn('tax_amount');
            $table->dropColumn('commission_amount');
            $table->dropColumn('commission_payer');
            $table->dropColumn('service_fee');
        });
    }
};
