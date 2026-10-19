<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionRecordingsTable extends Migration
{
    public function up()
    {
        Schema::create('auction_recordings', function (Blueprint $table) {
            $table->id();
            // Keep the audit/cleanup record even if an auction is deleted.
            $table->unsignedBigInteger('live_video_id')->unique();
            $table->string('driver');
            $table->string('disk');
            $table->string('app_id')->nullable();
            $table->string('bucket')->nullable();
            $table->string('channel');
            $table->string('uid');
            $table->string('prefix')->unique();
            $table->string('status')->default('pending')->index();
            $table->text('resource_id')->nullable();
            $table->string('sid')->nullable();
            $table->timestamp('acquired_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('retry_at')->nullable()->index();
            $table->unsignedInteger('failures')->default(0);
            $table->string('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auction_recordings');
    }
}
