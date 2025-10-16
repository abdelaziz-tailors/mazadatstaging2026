<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLiveVideoItemIdToVideoCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_comments', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('live_video_item_id')->nullable();
            $table->foreign('live_video_item_id')->references('id')->on('live_video_items')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_comments', function (Blueprint $table) {
            //
        });
    }
}
