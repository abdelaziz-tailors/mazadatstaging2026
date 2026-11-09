<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionBidWithdrawalsTable extends Migration
{
    public function up()
    {
        Schema::create('auction_bid_withdrawals', function (Blueprint $table) {
            $table->unsignedBigInteger('bid_id')->primary();
            $table->unsignedBigInteger('auction_id');
            $table->unsignedBigInteger('item_id');
            $table->timestamp('created_at');
            $table->timestamp('synced_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auction_bid_withdrawals');
    }
}
