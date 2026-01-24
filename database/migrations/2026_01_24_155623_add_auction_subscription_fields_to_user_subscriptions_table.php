<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuctionSubscriptionFieldsToUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->enum('subscription_type', ['monthly', 'annual'])->nullable()->after('package_id');
            $table->integer('auctions_limit')->default(0)->after('subscription_type');
            $table->integer('remaining_auctions')->default(0)->after('auctions_limit');
            $table->timestamp('expires_at')->nullable()->after('remaining_auctions');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('expires_at');
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['subscription_type', 'auctions_limit', 'remaining_auctions', 'expires_at', 'status', 'rejection_reason']);
        });
    }
}
