<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBidStateVersionsToLiveVideoItems extends Migration
{
    public function up()
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->unsignedBigInteger('bid_state_version')->default(0);
            $table->unsignedBigInteger('bid_state_synced_version')->default(0);
        });
    }

    public function down()
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->dropColumn(['bid_state_version', 'bid_state_synced_version']);
        });
    }
}
