<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VideoShareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable();
            $table->unsignedBigInteger('video_id')->nullable();;
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_shares');
    }
}
