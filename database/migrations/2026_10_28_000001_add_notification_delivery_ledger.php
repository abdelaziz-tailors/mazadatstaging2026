<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNotificationDeliveryLedger extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $required = ['live_videos', 'seller_submissions', 'orders', 'notifications'];
            $engines = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->whereIn('TABLE_NAME', $required)->pluck('ENGINE', 'TABLE_NAME');
            foreach ($required as $table) {
                if (strtolower($engines[$table] ?? '') !== 'innodb') {
                    throw new RuntimeException("Lifecycle notifications require InnoDB: {$table}. No engine conversion was attempted.");
                }
            }
        }
        foreach (['event_key', 'push_completed_at'] as $column) {
            if (Schema::hasColumn('notifications', $column)) {
                throw new RuntimeException('Notification delivery schema is already present or partially applied; inspect before retrying.');
            }
        }
        Schema::table('notifications', function (Blueprint $table) {
            // Nullable so legacy/manual notifications retain their existing behavior.
            $table->string('event_key', 64)->nullable()->unique();
            $table->timestamp('push_completed_at')->nullable();
            $table->index(['push_completed_at', 'event_key'], 'notifications_pending_push');
        });
    }

    public function down(): void
    {
        if (DB::table('notifications')->whereNotNull('event_key')->exists()) {
            throw new RuntimeException('Notification delivery receipts exist. Retain the schema and use a compatible forward fix.');
        }
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_pending_push');
            $table->dropUnique(['event_key']);
            $table->dropColumn(['event_key', 'push_completed_at']);
        });
    }
}
