<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->unsignedBigInteger('live_video_id');
            $table->unsignedBigInteger('buyer_id');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percent', 8, 2)->default(0);
            $table->decimal('tax_value', 12, 2)->default(0);
            $table->decimal('commission_percent', 8, 2)->default(0);
            $table->enum('commission_payer', ['buyer', 'seller'])->default('buyer');
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->decimal('service_fee_per_item', 12, 2)->default(0);
            $table->decimal('service_fee_total', 12, 2)->default(0);
            $table->decimal('piece_services_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparation',
                'ready_for_delivery',
                'shipping',
                'delivered',
            ])->default('pending');
            $table->string('payment_proof')->nullable();
            $table->text('shipping_address')->nullable();
            $table->unsignedBigInteger('shipping_city_id')->nullable();
            $table->string('shipping_lat')->nullable();
            $table->string('shipping_lng')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('live_video_id')->references('id')->on('live_videos')->cascadeOnDelete();
            $table->foreign('buyer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('shipping_city_id')->references('id')->on('cities')->nullOnDelete();
            $table->unique(['live_video_id', 'buyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
