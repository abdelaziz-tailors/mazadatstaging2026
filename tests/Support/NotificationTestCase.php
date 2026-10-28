<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Queue, Schema};
use Tests\TestCase;

/** No application database or application migration sweep is used by this suite. */
abstract class NotificationTestCase extends TestCase
{
    protected $mysqlConfig;
    protected function setUp(): void
    {
        parent::setUp();
        \Laravel\Telescope\Telescope::stopRecording();
        config(['database.default' => 'notifications_test', 'database.connections.notifications_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('notifications_test');
        if (getenv('NOTIFICATIONS_MYSQL_CONFIG')) {
            $this->mysqlConfig = json_decode(file_get_contents(getenv('NOTIFICATIONS_MYSQL_CONFIG')), true);
            $this->assertSame('mysql', $this->mysqlConfig['driver']);
            $this->assertSame('127.0.0.1', $this->mysqlConfig['host']);
            $this->assertSame(33328, (int) $this->mysqlConfig['port']);
            $this->assertSame('mazadat_notifications_test', $this->mysqlConfig['database']);
            $this->assertEmpty($this->mysqlConfig['url'] ?? null);
            $this->assertEmpty($this->mysqlConfig['unix_socket'] ?? null);
            config(['database.connections.notifications_test' => $this->mysqlConfig]);
            DB::purge('notifications_test');
        } else {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        }
        Queue::fake();
        $this->withoutMiddleware([\App\Http\Middleware\APISettings::class, 'APISettings']);
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name')->nullable(); $t->string('phone')->nullable(); $t->string('password')->nullable();
            $t->string('user_type')->nullable(); $t->string('gender')->nullable(); $t->string('app_lang')->nullable();
            $t->string('fcm_token')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('cities', function (Blueprint $t) { $t->id(); });
        Schema::create('live_videos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->unsignedBigInteger('partner_id')->nullable();
            $t->string('status')->nullable(); $t->string('title')->nullable(); $t->string('title_ar')->nullable();
            $t->string('date_start_at')->nullable(); $t->string('time_start_at')->nullable();
            $t->string('date_end_at')->nullable(); $t->string('time_end_at')->nullable();
            $t->timestamp('start_at')->nullable(); $t->timestamp('end_at')->nullable();
            $t->timestamp('upcoming_reminder_sent_at')->nullable(); $t->timestamps();
        });
        Schema::create('live_video_items', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('live_video_id'); $t->unsignedBigInteger('seller_id')->nullable();
            $t->unsignedBigInteger('user_finished_id')->nullable(); $t->unsignedBigInteger('transferred_from_item_id')->nullable();
            $t->decimal('finished_price')->nullable(); $t->timestamps();
            $t->string('status')->nullable();
        });
        foreach (['follow_users' => ['user_id', 'follow_id'], 'live_video_item_favorites' => ['user_id', 'live_video_item_id'],
            'live_video_users' => ['user_id', 'live_video_id']] as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($columns) {
                $t->id(); foreach ($columns as $column) { $t->unsignedBigInteger($column); } $t->timestamps();
            });
        }
        Schema::create('video_comments', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('video_id'); $t->unsignedBigInteger('live_video_item_id')->nullable();
            $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('comment_id')->nullable();
            $t->string('comment')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        require_once database_path('migrations/New folder/2024_06_06_123127_notifications_table.php');
        (new \NotificationsTable())->up();
        require_once database_path('migrations/2026_07_07_143935_add_user_fields_to_notifications_table.php');
        (new \AddUserFieldsToNotificationsTable())->up();
        (require database_path('migrations/2026_06_22_100000_create_orders_table.php'))->up();
        (require database_path('migrations/2026_04_13_120000_create_seller_submissions_tables.php'))->up();
        require_once database_path('migrations/2026_10_28_000001_add_notification_delivery_ledger.php');
        (new \AddNotificationDeliveryLedger())->up();
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        if ($this->mysqlConfig) {
            // Explicitly allow-listed, disposable schema only; no application DB sweep.
            $this->assertSame('mazadat_notifications_test', DB::connection()->getDatabaseName());
            foreach (['seller_submission_media', 'seller_submissions', 'orders', 'notifications', 'video_comments',
                'live_video_users', 'live_video_item_favorites', 'follow_users', 'live_video_items', 'live_videos', 'cities', 'users'] as $table) {
                Schema::dropIfExists($table);
            }
        }
        DB::disconnect('notifications_test');
        parent::tearDown();
    }
}
