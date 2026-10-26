<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSubscriptionRequestSnapshots extends Migration
{
    public function up()
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $required = ['users', 'user_subscriptions', 'packages', 'live_videos', 'notifications', 'live_video_items', 'live_video_item_pieces'];
            $tables = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->whereIn('TABLE_NAME', $required)->pluck('ENGINE', 'TABLE_NAME');
            foreach ($required as $table) {
                if (strtolower($tables[$table] ?? '') !== 'innodb') {
                    throw new RuntimeException("Subscription deployment requires InnoDB: {$table}. No engine conversion was attempted.");
                }
            }
            $indexes = collect(DB::select('SHOW INDEX FROM user_subscriptions'))->groupBy('Key_name');
            foreach ($indexes as $name => $columns) {
                $columns = $columns->sortBy('Seq_in_index')->pluck('Column_name')->values()->all();
                if ($name === 'user_subscriptions_user_id_status_index' || array_slice($columns, 0, 2) === ['user_id', 'status']) {
                    throw new RuntimeException('A subscription user/status index already exists; inspect it before applying duplicate DDL.');
                }
            }
        }
        foreach (['package_snapshot', 'duration_months', 'started_at', 'replaced_at'] as $column) {
            if (Schema::hasColumn('user_subscriptions', $column)) {
                throw new RuntimeException('Subscription snapshot schema already exists or is partially applied; inspect it before retrying.');
            }
        }
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->json('package_snapshot')->nullable();
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        if (DB::table('user_subscriptions')->whereNotNull('package_snapshot')->orWhereNotNull('duration_months')
            ->orWhereNotNull('started_at')->orWhereNotNull('replaced_at')->exists()) {
            throw new RuntimeException('Snapshot/lifecycle data exists. Retain the schema and use a compatible forward fix.');
        }
        if (DB::connection()->getDriverName() === 'mysql') {
            // InnoDB can remove the original FK index when the new covering index is added.
            $hasUserIndex = collect(DB::select('SHOW INDEX FROM user_subscriptions'))->contains(function ($index) {
                return $index->Key_name !== 'user_subscriptions_user_id_status_index'
                    && (int) $index->Seq_in_index === 1 && $index->Column_name === 'user_id';
            });
            if (!$hasUserIndex) {
                Schema::table('user_subscriptions', function (Blueprint $table) {
                    $table->index('user_id', 'user_subscriptions_user_id_foreign');
                });
            }
        }
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn(['package_snapshot', 'duration_months', 'started_at', 'replaced_at']);
        });
    }
}
