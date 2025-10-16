<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideosTypesToLiveVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->enum('type', ['live', 'recorded'])->default('live');
            $table->enum('partners_type', ['single', 'multiple'])->default('single');
            $table->string('video')->nullable();
            $table->integer('partner_id')->nullable();

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
            $table->dropColumn('type');
            $table->dropColumn('partners_type');
            $table->dropColumn('video');
            $table->dropColumn('partner_id');
        });
    }
}
