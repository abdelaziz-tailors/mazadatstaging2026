<?php

namespace Tests\Feature\Api\User;

use App\Models\User\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerUserControllerTest extends TestCase
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
            $table->string('user_type');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->softDeletes();
        });
        Schema::create('seller_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('status');
            $table->softDeletes();
        });
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->softDeletes();
        });
    }

    /** @dataProvider eligibility */
    public function test_seller_eligibility($status, $partnerId, $expected, $deletedAt = null): void
    {
        DB::table('users')->insert(['id' => 1, 'name' => 'Seller', 'user_type' => 'seller']);
        if ($status !== null) {
            DB::table('seller_submissions')->insert(['user_id' => 1, 'partner_id' => $partnerId,
                'status' => $status, 'deleted_at' => $deletedAt]);
        }
        $this->actingAs((new User())->forceFill(['id' => 100, 'user_type' => 'vendor']), 'api');

        $response = $this->getJson('/api/user/sellers')->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('code', 200)
            ->assertJsonPath('pagination.total', $expected)
            ->assertJsonCount($expected, 'data');
        if ($expected) {
            $response->assertJsonPath('data.0.id', 1);
        }
    }

    public static function eligibility(): array
    {
        return [
            'approved' => ['approved', 100, 1],
            'pending' => ['pending', 100, 0],
            'rejected' => ['rejected', 100, 0],
            'needs edit' => ['needs edit', 100, 0],
            'other subscriber' => ['approved', 200, 0],
            'no request' => [null, 100, 0],
            'deleted request' => ['approved', 100, 0, '2026-01-01 00:00:00'],
        ];
    }

    public function test_multiple_approvals_do_not_duplicate_sellers_or_include_inactive_users(): void
    {
        foreach ([['id' => 1, 'user_type' => 'seller', 'is_active' => 1],
            ['id' => 2, 'user_type' => 'seller', 'is_active' => 0],
            ['id' => 3, 'user_type' => 'vendor', 'is_active' => 1]] as $user) {
            DB::table('users')->insert($user);
            DB::table('seller_submissions')->insert(['user_id' => $user['id'], 'partner_id' => 100, 'status' => 'approved']);
        }
        DB::table('seller_submissions')->insert(['user_id' => 1, 'partner_id' => 100, 'status' => 'approved']);
        $this->actingAs((new User())->forceFill(['id' => 100]), 'api');
        $this->getJson('/api/user/sellers')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 1)->assertJsonPath('pagination.total', 1);
    }

    public function test_guest_cannot_see_unassigned_approved_requests(): void
    {
        DB::table('users')->insert(['id' => 1, 'user_type' => 'seller']);
        DB::table('seller_submissions')->insert(['user_id' => 1, 'partner_id' => null, 'status' => 'approved']);
        $this->getJson('/api/user/sellers')->assertOk()->assertJsonCount(0, 'data')
            ->assertJsonPath('pagination.total', 0);
    }
}
