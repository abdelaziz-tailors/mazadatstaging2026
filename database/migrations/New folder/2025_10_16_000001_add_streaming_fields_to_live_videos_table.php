<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStreamingFieldsToLiveVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->string('agora_channel_name')->nullable()->unique()->comment('Agora channel name');
            $table->text('agora_token_publisher')->nullable()->comment('Agora token for broadcaster');
            $table->text('agora_token_subscriber')->nullable()->comment('Agora token for viewers');
            $table->string('agora_app_id')->nullable()->comment('Agora App ID');
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
            $table->dropColumn(['agora_channel_name', 'agora_token_publisher', 'agora_token_subscriber', 'agora_app_id']);
        });
    }
}

