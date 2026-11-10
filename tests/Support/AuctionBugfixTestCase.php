<?php

namespace Tests\Support;

use App\Http\Controllers\FirebaseController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\SellerSubmission;
use App\Models\User\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Auth, DB, Schema};

abstract class AuctionBugfixTestCase extends NotificationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        app()->setLocale('en');
        config(['app.debug' => false, 'app.dashboard_locale' => 'en']);
        Schema::table('users', function (Blueprint $t) {
            $t->string('email')->nullable(); $t->string('user_name')->nullable();
            $t->boolean('is_verified')->default(false);
        });
        Schema::create('admins', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->string('type');
            $t->string('partner')->nullable(); $t->string('name')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::table('live_videos', function (Blueprint $t) {
            foreach (['type', 'partners_type', 'information', 'information_ar', 'terms_conditions', 'terms_conditions_ar',
                'image', 'start_price', 'commission_amount', 'commission_payer', 'service_fee', 'schedule_start_utc',
                'schedule_end_utc', 'schedule_timezone', 'agora_channel_name', 'agora_token', 'agora_app_id'] as $field) {
                $t->string($field)->nullable();
            }
            $t->unsignedBigInteger('admin_id')->nullable(); $t->unsignedBigInteger('city_id')->nullable();
        });
        Schema::table('live_video_items', function (Blueprint $t) {
            foreach (['title', 'title_ar', 'information', 'information_ar', 'image', 'category_id', 'color_id', 'type',
                'date_barth', 'animal_pen_id', 'health_certificate', 'video', 'address', 'terms', 'terms_ar',
                'bidding', 'quantity', 'piece_multiplier_number'] as $field) { $t->string($field)->nullable(); }
            $t->unsignedBigInteger('user_id')->nullable(); $t->boolean('show_partner_name')->default(false);
        });
        Schema::create('live_video_item_pieces', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('live_video_item_id'); $t->integer('piece_number');
            foreach (['age', 'gender', 'identifier', 'baham_count'] as $field) { $t->string($field)->nullable(); }
            $t->timestamps();
        });
        (require database_path('migrations/2026_06_22_100001_create_order_items_table.php'))->up();
        foreach (['ages', 'colors', 'categories', 'animal_pens'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id(); $t->json('name')->nullable(); $t->string('color')->nullable();
                $t->boolean('is_active')->default(true); $t->unsignedBigInteger('admin_id')->nullable();
                $t->timestamps(); $t->softDeletes();
            });
        }
        $this->app->instance(FirebaseController::class, \Mockery::mock(FirebaseController::class)->shouldIgnoreMissing());
    }

    protected function user(array $attributes = []): User
    {
        return User::create($attributes + ['name' => 'Fixture user', 'user_type' => 'seller', 'is_active' => 1, 'app_lang' => 'en']);
    }

    protected function admin(User $owner, string $type = 'partner'): Admin
    {
        $admin = Admin::create(['user_id' => $owner->id, 'type' => $type, 'name' => 'Reviewer']);
        Auth::guard('admin')->setUser($admin);
        return $admin;
    }

    protected function auction(User $owner, array $attributes = []): LiveVideo
    {
        return LiveVideo::create($attributes + ['user_id' => $owner->id, 'partner_id' => $owner->id,
            'status' => 'pending', 'type' => 'live', 'partners_type' => 'single', 'commission_payer' => 'buyer']);
    }

    protected function submission(User $seller, User $owner, string $status = 'pending'): SellerSubmission
    {
        return SellerSubmission::create(['user_id' => $seller->id, 'partner_id' => $owner->id,
            'sheep_type' => 'Sheep', 'age' => '2', 'expected_price' => 100, 'status' => $status]);
    }
}
