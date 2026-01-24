<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuctionSubscriptionFieldsToPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('subscription_type', ['monthly', 'annual'])->nullable()->after('is_active');
            $table->integer('auctions_limit')->default(0)->after('subscription_type');
            $table->double('monthly_price')->nullable()->after('auctions_limit');
            $table->double('annual_price')->nullable()->after('monthly_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['subscription_type', 'auctions_limit', 'monthly_price', 'annual_price']);
        });
    }
}
