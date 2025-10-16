<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_video_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('live_video_id')->nullable();
            $table->enum('status', ['pending','working','finished'])->nullable();
            $table->timestamp('end_at')->nullable();
            $table->text('image')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();;
            $table->text('information')->nullable();
            $table->double('weight')->nullable();
            $table->double('age')->nullable();
            $table->double('quantity')->nullable();
            $table->double('start_price')->nullable();
            $table->double('bidding')->nullable();
            $table->double('finished_price')->nullable();
            $table->unsignedBigInteger('user_finished_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('user_finished_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('live_video_id')->references('id')->on('live_videos')->onDelete('cascade');
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
        Schema::dropIfExists('live_video_items');
    }
}
