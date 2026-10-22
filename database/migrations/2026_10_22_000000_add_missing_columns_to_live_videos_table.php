<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('live_videos', 'title')) {
            Schema::table('live_videos', function (Blueprint $table) {
                $table->string('title', 300)->nullable();
            });
        }

        if (! Schema::hasColumn('live_videos', 'city_id')) {
            Schema::table('live_videos', function (Blueprint $table) {
                $table->integer('city_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        // These columns may predate this migration and contain auction data.
        // Keep them on rollback; reapplying up() is safe.
    }
};
