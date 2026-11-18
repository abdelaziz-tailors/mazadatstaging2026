<?php

namespace Tests\Feature\Api\User;

use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeletedPartnerVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('user_type')->default('vendor');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->softDeletes();
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->softDeletes();
        });
    }

    public function test_dashboard_soft_deletion_removes_partner_from_app_and_restore_reinstates_it(): void
    {
        DB::table('users')->insert(['id' => 10, 'name' => 'Partner']);
        DB::table('admins')->insert(['id' => 99, 'user_id' => 10, 'type' => 'partner']);
        DB::table('live_videos')->insert(['user_id' => 10, 'status' => 'start']);

        $this->getJson('/api/user/partners')->assertOk()
            ->assertJsonPath('data.0.id', 10)->assertJsonPath('data.0.active_auctions_count', 1);

        // Same soft-delete operation used by the dashboard destroy action.
        $admin = Admin::findOrFail(99);
        $admin->delete();

        $this->getJson('/api/user/partners')->assertOk()
            ->assertJsonCount(0, 'data')->assertJsonPath('pagination.total', 0);
        $this->assertDatabaseHas('users', ['id' => 10, 'deleted_at' => null]);
        $this->assertDatabaseHas('live_videos', ['user_id' => 10, 'status' => 'start']);

        $admin->restore();
        $this->getJson('/api/user/partners')->assertOk()->assertJsonPath('data.0.id', 10);
    }

    public function test_existing_deletions_are_filtered_before_pagination_without_changing_other_eligibility(): void
    {
        for ($id = 1; $id <= 15; $id++) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Partner '.$id]);
            DB::table('admins')->insert(['user_id' => $id, 'type' => 'partner',
                'deleted_at' => $id >= 4 ? '2026-11-01 00:00:00' : null]);
        }
        DB::table('users')->insert([
            ['id' => 16, 'name' => 'Inactive', 'user_type' => 'vendor', 'is_active' => 0, 'deleted_at' => null],
            ['id' => 17, 'name' => 'Buyer', 'user_type' => 'buyer', 'is_active' => 1, 'deleted_at' => null],
            ['id' => 18, 'name' => 'Deleted user', 'user_type' => 'vendor', 'is_active' => 1, 'deleted_at' => '2026-11-01 00:00:00'],
            ['id' => 19, 'name' => 'Unlinked vendor', 'user_type' => 'vendor', 'is_active' => 1, 'deleted_at' => null],
            ['id' => 20, 'name' => 'Other admin type', 'user_type' => 'vendor', 'is_active' => 1, 'deleted_at' => null],
        ]);
        DB::table('admins')->insert(['user_id' => 20, 'type' => 'admin', 'deleted_at' => '2026-11-01 00:00:00']);

        $response = $this->getJson('/api/user/partners')->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('pagination.total', 5)
            ->assertJsonPath('pagination.per_page', 10)->assertJsonCount(5, 'data');
        $this->assertSame([20, 19, 3, 2, 1], array_column($response->json('data'), 'id'));
    }
}
