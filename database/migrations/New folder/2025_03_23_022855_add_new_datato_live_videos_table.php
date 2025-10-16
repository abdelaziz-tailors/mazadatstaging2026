<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewDatatoLiveVideosTable extends Migration
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
            $table->date('date_start_at')->nullable();
            $table->date('date_end_at')->nullable();
            $table->time('time_start_at')->nullable();
            $table->time('time_end_at')->nullable();
            $table->text('terms_conditions')->nullable();
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
