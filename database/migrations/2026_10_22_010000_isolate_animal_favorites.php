<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('live_video_item_favorites')) {
            Schema::create('live_video_item_favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('live_video_item_id')->constrained('live_video_items')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'live_video_item_id']);
            });
        }

        // Existing API/model semantics are auctions, not the videos table.
        // Remove only the incorrect FK; never reinterpret or delete old rows.
        if (DB::getDriverName() === 'mysql') {
            $constraints = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
                 AND REFERENCED_TABLE_NAME = ?',
                [DB::connection()->getDatabaseName(), 'video_favorites', 'video_id', 'videos']
            );
            foreach ($constraints as $constraint) {
                Schema::table('video_favorites', function (Blueprint $table) use ($constraint) {
                    $table->dropForeign($constraint->CONSTRAINT_NAME);
                });
            }
        }
    }

    public function down(): void
    {
        // Preserve both legacy and animal favorites; do not restore the wrong FK.
    }
};
