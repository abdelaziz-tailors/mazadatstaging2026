<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentProofToLiveVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('winner_video');
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
            $table->dropColumn('payment_proof');
        });
    }
}
