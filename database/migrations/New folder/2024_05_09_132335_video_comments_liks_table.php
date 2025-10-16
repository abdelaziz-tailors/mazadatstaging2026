<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VideoCommentsLiksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('comment_id')->references('id')->on('video_comments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_comment_likes');
    }
}
