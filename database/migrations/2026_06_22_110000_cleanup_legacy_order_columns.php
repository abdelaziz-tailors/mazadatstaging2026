<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $legacyItemColumns = [
        'category_id',
        'start_price',
        'payment_status',
        'status_cart',
        'payment_proof',
        'age_id',
        'weight',
        'identifier',
        'age_type',
        'baham_count',
        'animal_pen_id',
        'color_id',
        'date_barth',
        'type',
        'health_certificate',
        'terms',
        'terms_ar',
    ];

    public function up(): void
    {
        $this->dropLegacyItemColumns();
        $this->dropShippingAddressesTable();
    }

    public function down(): void
    {
        Schema::create('shapping_addresses', function (Blueprint $table) {
            $table->id();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('live_video_item_id')->nullable();
            $table->foreign('live_video_item_id')->references('id')->on('live_video_items')->onDelete('cascade');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->timestamps();
        });

        Schema::table('live_video_items', function (Blueprint $table) {
            foreach ($this->legacyItemColumns as $column) {
                    if (!Schema::hasColumn('live_video_items', $column)) {
                        $table->addColumn($column, 'nullable');
                    }
            }
        });
    }

   


    protected function dropLegacyItemColumns(): void
    {
        foreach ($this->legacyItemColumns as $column) {
            if (Schema::hasTable('live_video_items') && Schema::hasColumn('live_video_items', $column)) {
                Schema::table('live_video_items', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    protected function dropShippingAddressesTable(): void
    {
        Schema::dropIfExists('shapping_addresses');
    }
};
