<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            if (! Schema::hasColumn('live_video_items', 'transferred_from_item_id')) {
                $table->unsignedBigInteger('transferred_from_item_id')->nullable()->after('live_video_id');
            }

            if (! Schema::hasColumn('live_video_items', 'transfer_origin_item_id')) {
                $table->unsignedBigInteger('transfer_origin_item_id')->nullable()->after('transferred_from_item_id');
            }
        });

        Schema::table('live_video_items', function (Blueprint $table) {
            if (! $this->foreignKeyExists('live_video_items_transferred_from_item_id_foreign')) {
                $table->foreign('transferred_from_item_id')
                    ->references('id')
                    ->on('live_video_items')
                    ->nullOnDelete();
            }

            if (! $this->foreignKeyExists('live_video_items_transfer_origin_item_id_foreign')) {
                $table->foreign('transfer_origin_item_id')
                    ->references('id')
                    ->on('live_video_items')
                    ->nullOnDelete();
            }

            if (! $this->indexExists('live_video_items_target_origin_unique')) {
                $table->unique(['live_video_id', 'transfer_origin_item_id'], 'live_video_items_target_origin_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            if ($this->indexExists('live_video_items_target_origin_unique')) {
                $table->dropUnique('live_video_items_target_origin_unique');
            }

            if ($this->foreignKeyExists('live_video_items_transfer_origin_item_id_foreign')) {
                $table->dropForeign(['transfer_origin_item_id']);
            }

            if ($this->foreignKeyExists('live_video_items_transferred_from_item_id_foreign')) {
                $table->dropForeign(['transferred_from_item_id']);
            }
        });

        Schema::table('live_video_items', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('live_video_items', 'transferred_from_item_id')) {
                $columns[] = 'transferred_from_item_id';
            }
            if (Schema::hasColumn('live_video_items', 'transfer_origin_item_id')) {
                $columns[] = 'transfer_origin_item_id';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'live_video_items')
            ->where('index_name', $indexName)
            ->exists();
    }

    private function foreignKeyExists(string $foreignKeyName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', 'live_video_items')
            ->where('constraint_name', $foreignKeyName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
