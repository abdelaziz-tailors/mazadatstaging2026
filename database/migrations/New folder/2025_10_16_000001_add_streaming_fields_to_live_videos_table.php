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
            $table->text('agora_token')->nullable()->comment('Agora token for broadcaster');
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
        // Reverse the current up() without deleting legacy token columns.
        $columns = array_values(array_filter(
            ['agora_channel_name', 'agora_token', 'agora_app_id'],
            fn ($column) => Schema::hasColumn('live_videos', $column)
        ));

        if ($columns !== []) {
            Schema::table('live_videos', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
}

