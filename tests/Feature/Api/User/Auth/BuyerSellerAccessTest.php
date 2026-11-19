<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\User\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;

class BuyerSellerAccessTest extends OptionalRegistrationEmailTest
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_18_000001_create_user_access_roles.php'))->up();
    }

    private function account(string $role = 'buyer', array $attributes = []): User
    {
        return User::create(array_merge(['name' => 'Original Owner', 'user_name' => 'owner',
            'phone' => '+966512345678', 'email' => 'owner@example.com', 'user_type' => $role,
            'password' => bcrypt('secret123'), 'is_verified' => true, 'is_active' => true], $attributes));
    }

    private function registration(string $role, array $attributes = []): array
    {
        return array_merge(['name' => 'Changed Name', 'user_name' => 'owner', 'email' => 'owner@example.com',
            'phone' => '0512345678', 'password' => 'secret123', 'password_confirmation' => 'secret123',
            'user_type' => $role], $attributes);
    }

    public static function directions(): array { return [['buyer', 'seller'], ['seller', 'buyer']]; }

    /** @dataProvider directions */
    public function test_second_role_requires_otp_preserves_identity_and_allows_both_logins($original, $requested): void
    {
        $user = $this->account($original);
        $before = $user->fresh()->getRawOriginal();
        $this->postJson('/api/user/login', ['phone' => $user->phone, 'password' => 'secret123', 'user_type' => $requested])
            ->assertStatus(422)->assertJsonPath('success', false);
        $this->postJson('/api/user/register', $this->registration($requested))->assertJsonPath('success', true);
        $this->assertFalse($user->fresh()->hasAccessRole($requested));
        $otp = UserOtp::where('type', 'role_access')->firstOrFail();
        $this->postJson('/api/user/verify-account', ['phone' => '00966512345678', 'otp' => 'wrong'])->assertStatus(403);
        $this->assertFalse($user->fresh()->hasAccessRole($requested));
        $this->postJson('/api/user/verify-account', ['phone' => '00966512345678', 'otp' => $otp->otp])
            ->assertJsonPath('success', true)->assertJsonPath('data.id', $user->id)->assertJsonPath('data.active_role', $requested);
        $this->assertSame($before, $user->fresh()->getRawOriginal());
        $this->assertSame(1, User::count());
        foreach ([$original, $requested] as $role) {
            $this->postJson('/api/user/login', ['phone' => '512345678', 'password' => 'secret123', 'user_type' => $role])
                ->assertJsonPath('success', true)->assertJsonPath('data.id', $user->id)->assertJsonPath('data.active_role', $role);
        }
        $this->postJson('/api/user/login', ['phone' => $user->phone, 'password' => 'secret123', 'user_type' => 'vendor'])
            ->assertStatus(422);
        $this->postJson('/api/user/register', $this->registration($requested))->assertJsonPath('success', true);
        $otp = UserOtp::where('type', 'role_access')->firstOrFail();
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])->assertJsonPath('success', true);
        $this->assertSame(1, DB::table('user_access_roles')->count());
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])->assertJsonPath('success', false);
    }

    public function test_invalid_password_unverified_inactive_and_vendor_accounts_cannot_link(): void
    {
        $user = $this->account();
        $this->postJson('/api/user/register', $this->registration('seller', ['password' => 'wrong123', 'password_confirmation' => 'wrong123']))->assertStatus(422);
        foreach ([['is_verified' => false], ['is_active' => false], ['user_type' => 'vendor']] as $attributes) {
            $user->update(array_merge(['is_verified' => true, 'is_active' => true, 'user_type' => 'buyer'], $attributes));
            $this->postJson('/api/user/register', $this->registration('seller'))->assertStatus(422);
        }
        $this->assertSame(0, DB::table('user_access_roles')->count());
        $this->assertSame(0, UserOtp::count());
    }

    public function test_expiry_password_change_and_deactivation_invalidate_pending_access(): void
    {
        $user = $this->account();
        foreach (['expiry', 'password', 'status'] as $change) {
            $user->update(['password' => bcrypt('secret123'), 'is_active' => true]);
            $this->postJson('/api/user/register', $this->registration('seller'))->assertJsonPath('success', true);
            $otp = UserOtp::where('type', 'role_access')->firstOrFail();
            if ($change === 'expiry') { $otp->update(['expire_at' => now()->subMinute()]); }
            if ($change === 'password') { $user->update(['password' => bcrypt('different123')]); }
            if ($change === 'status') { $user->update(['is_active' => false]); }
            $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])->assertStatus(403);
        }
        $this->assertSame(0, DB::table('user_access_roles')->count());
    }

    public function test_membership_constraint_and_seller_query(): void
    {
        $user = $this->account();
        $this->assertSame(0, User::withAccessRole('seller')->count());
        DB::table('user_access_roles')->insert(['user_id' => $user->id, 'role' => 'seller']);
        $this->assertSame(1, User::withAccessRole('seller')->count());
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('user_access_roles')->insert(['user_id' => $user->id, 'role' => 'seller']);
    }
    public function test_unverified_inactive_wrong_password_and_ambiguous_login_are_rejected(): void
    {
        $user = $this->account();
        foreach ([['is_verified' => false], ['is_active' => false]] as $attributes) {
            $user->update(array_merge(['is_verified' => true, 'is_active' => true], $attributes));
            $this->postJson('/api/user/login', ['phone' => $user->phone, 'password' => 'secret123', 'user_type' => 'buyer'])
                ->assertJsonPath('success', false);
        }
        $user->update(['is_verified' => true, 'is_active' => true]);
        $this->postJson('/api/user/login', ['phone' => $user->phone, 'password' => 'wrong', 'user_type' => 'buyer'])
            ->assertJsonPath('success', false);
        DB::statement('DROP INDEX users_registration_phone_unique');
        User::create(['phone' => '0512345678', 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'is_verified' => true]);
        $this->postJson('/api/user/login', ['phone' => $user->phone, 'password' => 'secret123', 'user_type' => 'buyer'])
            ->assertJsonPath('success', false);
    }

    /** @dataProvider directions */
    public function test_new_selected_role_registration($role, $unused): void
    {
        $this->postJson('/api/user/register', $this->registration($role))->assertJsonPath('success', true);
        $otp = UserOtp::where('type', 'register')->firstOrFail();
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])
            ->assertJsonPath('success', true)->assertJsonPath('data.user_type', $role);
        $this->assertSame([$role], User::first()->availableRoles());
    }

    public function test_api_key_and_seller_invoice_authorization_remain_enforced(): void
    {
        $this->withMiddleware('APISettings');
        $this->postJson('/api/user/register', $this->registration('buyer'))->assertJsonPath('code', 403);
        $this->withHeaders(['x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM', 'Accept-Language' => 'en'])
            ->postJson('/api/user/register', $this->registration('buyer'))->assertJsonPath('success', true);
        $user = $this->account();
        $this->actingAs($user, 'api')->getJson('/api/user/auction/seller-invoice-list')->assertStatus(403);
    }

}
