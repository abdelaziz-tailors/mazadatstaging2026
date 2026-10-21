<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_services', function (Blueprint $table) {
            $table->string('cost_pricing_method', 24)->default('fixed');
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Removing catalog pricing configuration requires a separately approved, data-preserving plan.');
    }
};
