<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consignor (seller) for settlement / invoice — distinct from vendor / partner (user_id on item).
     */
    public function up(): void
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->after('user_id');
            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn('seller_id');
        });
    }
};
