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
    public function test_registration_creates_independent_account_not_role_membership($original, $requested): void
    {
        $user = $this->account($original);
        $before = $user->fresh()->getRawOriginal();
        $payload = $this->registration($requested, ['user_name' => 'independent', 'email' => 'owner@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123']);
        $this->postJson('/api/user/register', $payload)->assertJsonPath('success', true);
        $otp = UserOtp::where('type', 'register')->firstOrFail();
        $this->assertSame('1111', $otp->otp);
        $this->assertSame(0, UserOtp::where('type', 'role_access')->count());
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'user_type' => $requested, 'otp' => '1111'])
            ->assertJsonPath('success', true)->assertJsonPath('data.user_type', $requested);
        $this->assertSame(2, User::count());
        $after = $user->fresh()->getRawOriginal(); unset($after['credential_group_id'], $before['credential_group_id']); $this->assertSame($before, $after);
        $this->assertSame(0, DB::table('user_access_roles')->count());
        foreach ([[$original, 'secret123', $user->id], [$requested, 'secret123', User::where('user_type', $requested)->firstOrFail()->id]] as [$role, $password, $id]) {
            $this->postJson('/api/user/login', ['phone' => '0512345678', 'user_type' => $role, 'password' => $password])
                ->assertJsonPath('success', true)->assertJsonPath('data.id', $id);
        }
        $this->postJson('/api/user/register', $payload)->assertStatus(422);
    }

    public function test_registration_endpoint_rejects_legacy_role_access_without_modifying_it(): void
    {
        $user = $this->account();
        $pending = UserOtp::create(['phone' => $user->phone, 'type' => 'role_access', 'user_type' => 'seller',
            'password' => hash('sha256', $user->id.':'.$user->password), 'otp' => '1111', 'expire_at' => now()->addMinutes(10)]);
        $before = $pending->fresh()->getRawOriginal();
        $this->postJson('/api/user/verify-account', ['phone' => $user->phone, 'user_type' => 'seller', 'otp' => '1111'])
            ->assertJsonPath('success', false);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->assertFalse($user->fresh()->hasAccessRole('seller'));
        $this->assertSame(1, User::count());
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
        DB::statement('DROP INDEX users_registration_phone_role_unique');
        User::create(['phone' => '0512345678', 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'is_verified' => true]);
        $this->postJson('/api/user/login', ['phone' => $user->phone, 'password' => 'secret123', 'user_type' => 'buyer'])
            ->assertJsonPath('success', false);
    }

    /** @dataProvider directions */
    public function test_new_selected_role_registration($role, $unused): void
    {
        $this->postJson('/api/user/register', $this->registration($role))->assertJsonPath('success', true);
        $otp = UserOtp::where('type', 'register')->firstOrFail();
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => $otp->otp])
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


    public function test_all_supported_types_can_register_independent_accounts_on_one_phone(): void
    {
        $ids = [];
        foreach (['buyer', 'seller', 'buyer_vendor', 'vendor'] as $role) {
            $payload = $this->registration($role, ['user_name' => 'separate_'.$role, 'email' => null]);
            if ($role === 'vendor') {
                $payload['commercial_register'] = \Illuminate\Http\UploadedFile::fake()->create('account-test.pdf', 1, 'application/pdf');
            }
            $this->post('/api/user/register', $payload)->assertOk()->assertJsonPath('success', true);
            $pending = UserOtp::where('type', 'register')->where('user_type', $role)->firstOrFail();
            if ($pending->commercial_register) {
                unlink(storage_path('app/public/vendor-commercial-files/'.basename($pending->commercial_register)));
            }
            $response = $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => '0512345678', 'user_type' => $role, 'otp' => '1111'])
                ->assertJsonPath('success', true)->assertJsonPath('data.user_type', $role);
            $ids[] = $response->json('data.id');
            $this->postJson('/api/user/login', ['phone' => '00966512345678', 'user_type' => $role, 'password' => 'secret123'])
                ->assertJsonPath('data.id', end($ids));
        }
        $this->assertCount(4, array_unique($ids));
        $this->assertSame(4, User::count());
        $this->assertSame(0, DB::table('user_access_roles')->count());
    }

    public function test_latest_pending_supersedes_older_account_and_consumes_once(): void
    {
        foreach (['buyer', 'seller'] as $role) {
            $this->postJson('/api/user/register', $this->registration($role, ['user_name' => $role, 'email' => null]))
                ->assertJsonPath('success', true);
        }
        $this->assertSame(1, UserOtp::count());
        $this->assertSame(2, UserOtp::withTrashed()->count());
        $this->postJson('/api/user/verify-account', ['phone' => '0512345678', 'otp' => '1111'])
            ->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'seller');
        $this->postJson('/api/user/verify-account', ['phone' => '0512345678', 'otp' => '1111'])->assertJsonPath('success', false);
        $this->assertSame(1, User::count());
    }
    public function test_shared_phone_login_validates_role_password_and_token_owner(): void
    {
        $buyer = $this->account();
        $seller = $this->account('seller', ['user_name' => 'seller', 'email' => 'seller@example.com', 'password' => bcrypt('seller-secret')]);
        $this->mock(\Laravel\Passport\PersonalAccessTokenFactory::class, function ($mock) use ($buyer, $seller) {
            $mock->shouldReceive('make')->with($buyer->id, 'MyApp', [])->once()
                ->andReturn(new \Laravel\Passport\PersonalAccessTokenResult('buyer-token', new \Laravel\Passport\Token()));
            $mock->shouldReceive('make')->with($seller->id, 'MyApp', [])->once()
                ->andReturn(new \Laravel\Passport\PersonalAccessTokenResult('seller-token', new \Laravel\Passport\Token()));
        });
        foreach ([['buyer', 'secret123', $buyer->id, 'buyer-token'], ['seller', 'seller-secret', $seller->id, 'seller-token']] as [$role, $password, $id, $token]) {
            $this->postJson('/api/user/login', ['phone' => '0512345678', 'user_type' => $role, 'password' => $password])
                ->assertJsonPath('data.id', $id)->assertJsonPath('data.token', $token);
        }
        foreach ([['seller', 'secret123'], ['buyer', 'seller-secret'], ['vendor', 'secret123']] as [$role, $password]) {
            $this->postJson('/api/user/login', ['phone' => '0512345678', 'user_type' => $role, 'password' => $password])
                ->assertJsonPath('success', false);
        }
        foreach ([null, 'subscriber', ['buyer']] as $invalid) {
            $this->postJson('/api/user/login', ['phone' => '0512345678', 'user_type' => $invalid, 'password' => 'secret123'])
                ->assertJsonPath('success', false)->assertJsonPath('code', 422);
        }
    }

    public function test_email_login_is_role_scoped_and_ambiguous_same_role_fails(): void
    {
        $buyer = $this->account();
        $seller = $this->account('seller', ['user_name' => 'seller', 'email' => 'seller@example.com']);
        $this->postJson('/api/user/login', ['email' => 'SELLER@example.com', 'user_type' => 'seller', 'password' => 'secret123'])
            ->assertJsonPath('data.id', $seller->id);
        $this->postJson('/api/user/login', ['email' => $seller->email, 'user_type' => 'buyer', 'password' => 'secret123'])
            ->assertJsonPath('success', false);
        // The database scopes email uniqueness by type; ambiguous historical same-type data fails closed.
        DB::statement('DROP INDEX users_shared_email_type_unique');
        $seller->update(['email' => $buyer->email]);
        $this->postJson('/api/user/login', ['email' => $buyer->email, 'user_type' => 'seller', 'password' => 'secret123'])
            ->assertJsonPath('data.id', $seller->id);
        $this->account('buyer', ['phone' => '+966588888888', 'user_name' => 'duplicate']);
        $this->postJson('/api/user/login', ['email' => $buyer->email, 'user_type' => 'buyer', 'password' => 'secret123'])
            ->assertJsonPath('success', false);
    }

    public function test_shared_phone_recovery_and_profile_updates_are_account_scoped(): void
    {
        $buyer = $this->account();
        $seller = $this->account('seller', ['user_name' => 'seller', 'email' => null]);
        $before = $buyer->fresh()->getRawOriginal();
        $this->postJson('/api/user/forgot-password', ['phone' => '0512345678'])->assertJsonPath('success', false);
        $this->postJson('/api/user/forgot-password', ['phone' => '0512345678', 'user_type' => 'seller'])->assertJsonPath('success', true);
        $this->assertSame('1111', $seller->fresh()->password_otp);
        $reset = ['phone' => '0512345678', 'otp' => '1111', 'password' => 'changed123', 'password_confirmation' => 'changed123'];
        $this->postJson('/api/user/reset-password', $reset)->assertJsonPath('success', false);
        $this->postJson('/api/user/reset-password', $reset + ['user_type' => 'seller'])->assertJsonPath('data.id', $seller->id);
        $this->assertSame($before, $buyer->fresh()->getRawOriginal());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('changed123', $seller->fresh()->password));
        $this->postJson('/api/user/reset-password', $reset + ['user_type' => 'seller'])->assertJsonPath('success', false);
        $this->actingAs($seller, 'api')->postJson('/api/user/profile_completed', ['phone' => '0512345678',
            'user_name' => 'seller-updated', 'email' => null, 'birth_date' => '1990-01-01', 'user_id' => $buyer->id])
            ->assertJsonPath('success', true);
        $this->assertSame($before, $buyer->fresh()->getRawOriginal());
    }

    public function test_migration_audit_is_idempotent_and_refuses_same_role_conflicts_without_changes(): void
    {
        $migration = require database_path('migrations/2026_11_24_000001_scope_account_phone_uniqueness_by_type.php');
        $this->assertSame([], $migration->audit());
        $this->account();
        DB::statement('DROP INDEX users_registration_phone_role_unique');
        $this->account('buyer', ['phone' => '0512345678', 'email' => null, 'user_name' => 'duplicate']);
        $before = DB::table('users')->get()->toArray();
        try { $migration->audit(); $this->fail('Conflicts must stop migration'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('conflicts', $e->getMessage()); }
        $this->assertEquals($before, DB::table('users')->get()->toArray());
    }

    public function test_logout_revokes_only_current_token_then_login_uses_independent_account(): void
    {
        \Illuminate\Support\Facades\Schema::create('oauth_access_tokens', function ($table) {
            $table->string('id')->primary(); $table->unsignedBigInteger('user_id');
            $table->boolean('revoked')->default(false); $table->timestamps();
        });
        $buyer = $this->account();
        $seller = $this->account('seller', ['user_name' => 'seller', 'email' => null, 'password' => bcrypt('seller-secret')]);
        $buyerToken = \Laravel\Passport\Token::create(['id' => 'buyer-token', 'user_id' => $buyer->id, 'revoked' => false]);
        $sellerToken = \Laravel\Passport\Token::create(['id' => 'seller-token', 'user_id' => $seller->id, 'revoked' => false]);
        $push = \Mockery::mock(new \App\Services\PushTokenOwnership());
        $push->shouldReceive('unregister')->once()->with($buyer->id);
        app()->instance(\App\Services\PushTokenOwnership::class, $push);
        $this->actingAs($buyer->withAccessToken($buyerToken), 'api')->postJson('/api/user/logout')->assertJsonPath('success', true);
        $this->assertTrue((bool) $buyerToken->fresh()->revoked);
        $this->assertFalse((bool) $sellerToken->fresh()->revoked);
        $this->app['auth']->forgetGuards();
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        $this->postJson('/api/user/login', ['phone' => '0512345678', 'user_type' => 'seller', 'password' => 'seller-secret'])
            ->assertJsonPath('success', true)->assertJsonPath('data.id', $seller->id)->assertJsonPath('data.user_type', 'seller');
    }

    public function test_legacy_role_challenge_keeps_expiry_and_password_binding(): void
    {
        $user = $this->account();
        foreach (['expired', 'password-changed', 'inactive'] as $case) {
            $user->update(['password' => bcrypt('secret123'), 'is_active' => true]);
            $pending = UserOtp::withTrashed()->updateOrCreate(['phone' => $user->phone, 'type' => 'role_access', 'user_type' => 'seller'],
                ['password' => hash('sha256', $user->id.':'.$user->password), 'otp' => '1111', 'expire_at' => now()->addMinutes(10)]);
            if ($case === 'expired') { $pending->update(['expire_at' => now()]); }
            if ($case === 'password-changed') { $user->update(['password' => bcrypt('different123')]); }
            if ($case === 'inactive') { $user->update(['is_active' => false]); }
            $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $user->phone, 'user_type' => 'seller', 'otp' => '1111'])
                ->assertJsonPath('success', false);
        }
        $this->assertSame(0, DB::table('user_access_roles')->count());
    }
}
