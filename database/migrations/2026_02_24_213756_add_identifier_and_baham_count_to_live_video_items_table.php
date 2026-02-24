<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdentifierAndBahamCountToLiveVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->string('piece_multiplier_number')->nullable()->after('quantity'); // رقم مضاعفه القطعه  
            $table->string('identifier')->nullable()->after('piece_multiplier_number'); // التعريف
            $table->string('baham_count')->nullable()->after('identifier'); // عدد للبهم
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
            $table->dropColumn(['piece_multiplier_number', 'identifier', 'baham_count']);
        });
    }
}
