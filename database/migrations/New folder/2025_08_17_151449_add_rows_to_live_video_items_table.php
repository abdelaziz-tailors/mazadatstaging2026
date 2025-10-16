<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRowsToLiveVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_video_items', function (Blueprint $table) {


            $table->string('age_type')->nullable();
            $table->text('address')->nullable();
            $table->string('health_certificate')->nullable();
            $table->string('terms')->nullable();
            $table->string('terms_ar')->nullable();
            $table->string('video')->nullable();

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
