<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('item_services', 'is_default')) {
            Schema::table('item_services', function (Blueprint $table) {
                $table->boolean('is_default')
                    ->default(false)
                    ->after('is_active')
                    ->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('item_services', 'is_default')) {
            Schema::table('item_services', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
};
