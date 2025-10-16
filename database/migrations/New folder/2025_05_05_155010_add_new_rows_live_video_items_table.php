<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewRowsLiveVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->text('title_ar')->nullable();
            $table->text('information_ar')->nullable();
            $table->date('date_barth');
            $table->enum('type', ['male','female'])->nullable();

            $table->unsignedBigInteger('color_id')->nullable();
            $table->unsignedBigInteger('age_id')->nullable();
            $table->unsignedBigInteger('animal_pen_id')->nullable();

            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
            $table->foreign('age_id')->references('id')->on('ages')->onDelete('cascade');
            $table->foreign('animal_pen_id')->references('id')->on('animal_pens')->onDelete('cascade');



            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            //
        });
    }
}
