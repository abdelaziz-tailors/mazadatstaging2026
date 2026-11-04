<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->dateTime('schedule_start_utc')->nullable();
            $table->dateTime('schedule_end_utc')->nullable();
            $table->string('schedule_timezone', 64)->nullable();
        });
    }
    public function down()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->dropColumn(['schedule_start_utc', 'schedule_end_utc', 'schedule_timezone']);
        });
    }
};
