<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('item_service_id')->nullable()->constrained('item_services')->nullOnDelete();
            $table->json('custom_name')->nullable();
            $table->decimal('price', 12, 2);

            $table->timestamps();
        });

    
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_services');
    }
};
