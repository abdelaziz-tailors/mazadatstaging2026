<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auction start price lives on live_videos (auction level).
     */
    public function up(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            if (! Schema::hasColumn('live_videos', 'start_price')) {
                $table->double('start_price')->nullable()->after('service_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            if (Schema::hasColumn('live_videos', 'start_price')) {
                $table->dropColumn('start_price');
            }
        });
    }
};
