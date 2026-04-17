<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add "photo" auction type (مزادات الصور) to live_videos.type enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE live_videos MODIFY COLUMN type ENUM('live', 'recorded', 'photo') NOT NULL DEFAULT 'live'");
    }

    public function down(): void
    {
        DB::statement("UPDATE live_videos SET type = 'live' WHERE type = 'photo'");
        DB::statement("ALTER TABLE live_videos MODIFY COLUMN type ENUM('live', 'recorded') NOT NULL DEFAULT 'live'");
    }
};
