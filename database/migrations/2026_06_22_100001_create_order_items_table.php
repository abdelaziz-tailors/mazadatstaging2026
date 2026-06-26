<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('live_video_item_id');
            $table->decimal('finished_price', 12, 2)->default(0);
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('live_video_item_id')->references('id')->on('live_video_items')->cascadeOnDelete();
            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
            $table->unique('live_video_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
