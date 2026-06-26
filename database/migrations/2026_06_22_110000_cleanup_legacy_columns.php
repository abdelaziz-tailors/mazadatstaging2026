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

    protected array $legacyItemForeignKeyColumns = [
        'category_id',
        'age_id',
        'color_id',
        'animal_pen_id',
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
                        if($column == 'age_id') {
                            $table->foreign('age_id')->references('id')->on('ages')->onDelete('cascade');
                        }
                        if($column == 'color_id') {
                            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
                        }
                        if($column == 'animal_pen_id') {
                            $table->foreign('animal_pen_id')->references('id')->on('animal_pens')->onDelete('cascade');
                        }
                        $table->addColumn($column, 'nullable');
                    }
            }
        });
    }

   


    protected function dropLegacyItemColumns(): void
    {
        if (! Schema::hasTable('live_video_items')) {
            return;
        }

        $columnsToDrop = array_values(array_filter(
            $this->legacyItemColumns,
            fn (string $column) => Schema::hasColumn('live_video_items', $column)
        ));

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('live_video_items', function (Blueprint $table) use ($columnsToDrop) {
            foreach ($columnsToDrop as $column) {
                if (in_array($column, $this->legacyItemForeignKeyColumns, true)) {
                    $table->dropForeign([$column]);
                }
            }

            $table->dropColumn($columnsToDrop);
        });
    }

    protected function dropShippingAddressesTable(): void
    {
        Schema::dropIfExists('shapping_addresses');
    }
};
