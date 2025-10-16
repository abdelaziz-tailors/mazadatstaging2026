<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRowLiveVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            //
            $table->text('image')->nullable();
//            $table->unsignedBigInteger('category_id')->nullable();;
            $table->text('information')->nullable();
//            $table->double('weight')->nullable();
//            $table->double('age')->nullable();
//            $table->double('quantity')->nullable();
//            $table->double('start_price')->nullable();
//            $table->double('bidding')->nullable();
//            $table->dateTime('auction_time')->nullable();
//            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            //
        });
    }
}
