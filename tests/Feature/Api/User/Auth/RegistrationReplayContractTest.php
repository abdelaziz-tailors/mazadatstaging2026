<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\User\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Legacy payload and explicitly accepted latest-pending replay behavior. */
class RegistrationReplayContractTest extends OptionalRegistrationEmailTest
{
    private function issue(array $overrides = [])
    {
        return $this->postJson('/api/user/register', array_merge([
            'name' => 'Shared Owner', 'user_name' => 'shared_buyer', 'email' => 'shared@example.com',
            'phone' => '0512345678', 'password' => 'secret123',
            'password_confirmation' => 'secret123', 'user_type' => 'buyer',
        ], $overrides));
    }

    private function verify(array $overrides = [])
    {
        return $this->postJson('/api/user/verify-account', array_merge(['phone' => '0512345678', 'otp' => '1111'], $overrides));
    }

    public function test_old_payload_activates_siblings_and_identical_old_request_may_complete_seller(): void
    {
        $this->issue()->assertJsonPath('success', true);
        $this->verify()->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'buyer');
        $buyer = User::firstOrFail(); $before = $buyer->getRawOriginal();
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->verify()->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'seller');
        $seller = User::where('user_type', 'seller')->firstOrFail();
        $this->assertNotSame($buyer->id, $seller->id);
        $this->assertSame($buyer->credential_group_id, $seller->credential_group_id);
        $this->assertSame($before, $buyer->fresh()->getRawOriginal());
        $this->verify()->assertJsonPath('success', false);
        foreach ([$buyer, $seller] as $user) {
            foreach (['phone' => '0512345678', 'email' => 'SHARED@example.com'] as $field => $value) {
                $this->postJson('/api/user/login', [$field => $value, 'user_type' => $user->user_type, 'password' => 'secret123'])
                    ->assertJsonPath('success', true)->assertJsonPath('data.id', $user->id);
            }
        }
    }

    public function test_newest_registration_supersedes_older_and_expiry_never_falls_back(): void
    {
        $this->issue()->assertJsonPath('success', true);
        $old = UserOtp::firstOrFail();
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->assertTrue($old->fresh()->trashed());
        $this->assertSame(1, UserOtp::count());
        UserOtp::firstOrFail()->update(['expire_at' => now()]);
        $this->verify()->assertStatus(403)->assertJsonPath('success', false);
        $this->assertSame(0, User::count());
    }

    public function test_legacy_multiple_pending_tie_breaker_and_no_consumed_fallback(): void
    {
        $this->issue()->assertJsonPath('success', true);
        $old = UserOtp::firstOrFail();
        $new = $old->replicate(['registration_phone_key', 'multi_account_phone_key']); $new->user_type = 'seller'; $new->user_name = 'legacy_seller';
        $new->registration_challenge_hash = null; $new->save();
        $this->verify()->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'seller');
        $this->verify()->assertJsonPath('success', false);
        $this->assertTrue($old->fresh()->trashed());
        $this->assertSame(1, User::count());
    }

    public function test_null_challenge_is_compatible_and_other_workflows_are_untouched(): void
    {
        $this->issue()->assertJsonPath('success', true);
        UserOtp::firstOrFail()->update(['registration_challenge_hash' => null]);
        $role = UserOtp::create(['phone' => '+966512345678', 'user_type' => 'seller', 'type' => 'role_access',
            'otp' => '1111', 'expire_at' => now()->addMinutes(10)]);
        $before = $role->fresh()->getRawOriginal();
        $this->verify()->assertJsonPath('success', true);
        $this->assertSame($before, $role->fresh()->getRawOriginal());
    }

    public function test_wrong_password_and_conflicting_identifiers_do_not_link(): void
    {
        $this->issue()->assertJsonPath('success', true); $this->verify()->assertJsonPath('success', true);
        foreach ([['password' => 'wrong123', 'password_confirmation' => 'wrong123'],
            ['email' => 'different@example.com'], ['phone' => '0599999999']] as $conflict) {
            $this->issue(array_merge(['user_type' => 'seller', 'user_name' => 'shared_seller'], $conflict))->assertStatus(422);
        }
        $this->assertSame(1, User::count()); $this->assertSame(0, UserOtp::count());
    }

    public function test_historical_matching_accounts_are_not_automatically_grouped(): void
    {
        foreach (['buyer', 'seller'] as $type) {
            User::create(['phone' => '+966512345678', 'email' => 'shared@example.com', 'user_name' => 'old_'.$type,
                'user_type' => $type, 'password' => bcrypt('secret123'), 'is_verified' => true]);
        }
        $this->issue(['user_type' => 'buyer_vendor', 'user_name' => 'third'])->assertStatus(422);
        $this->assertSame(0, DB::table('credential_groups')->count());
        $this->assertSame(2, User::whereNull('credential_group_id')->count());
    }

    public function test_shared_credential_edits_propagate_and_invalidate_staged_credentials(): void
    {
        $this->issue()->assertJsonPath('success', true); $this->verify()->assertJsonPath('success', true);
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->verify()->assertJsonPath('success', true);
        $this->issue(['user_type' => 'buyer_vendor', 'user_name' => 'third'])->assertJsonPath('success', true);
        $buyer = User::where('user_type', 'buyer')->firstOrFail();
        $buyer->update(['password' => bcrypt('changed123'), 'email' => 'changed@example.com', 'phone' => '+966599999999']);
        foreach (User::all() as $user) {
            $this->assertTrue(Hash::check('changed123', $user->password));
            $this->assertSame('changed@example.com', $user->email);
            $this->assertSame('+966599999999', $user->phone);
        }
        $this->verify()->assertStatus(403)->assertJsonPath('success', false);
        $this->assertSame(2, User::count());
    }

    public function test_untyped_recovery_updates_both_accounts_and_consumes_group_reset_state(): void
    {
        $this->issue()->assertJsonPath('success', true); $this->verify()->assertJsonPath('success', true);
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->verify()->assertJsonPath('success', true);
        $this->postJson('/api/user/forgot-password', ['phone' => '0512345678'])->assertJsonPath('success', true);
        $this->postJson('/api/user/reset-password', ['phone' => '0512345678', 'otp' => '1111',
            'password' => 'recovered123', 'password_confirmation' => 'recovered123'])->assertJsonPath('success', true);
        foreach (User::all() as $user) {
            $this->assertTrue(Hash::check('recovered123', $user->password));
            $this->assertNull($user->password_otp);
        }
        $this->postJson('/api/user/reset-password', ['phone' => '0512345678', 'otp' => '1111',
            'password' => 'again123', 'password_confirmation' => 'again123'])->assertJsonPath('success', false);
    }

    public function test_optional_challenge_mismatch_cannot_select_superseded_registration(): void
    {
        $old = $this->issue()->assertJsonPath('success', true)->json('data.challenge_id');
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->verify(['challenge_id' => $old])->assertJsonPath('success', false);
        $this->verify()->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'seller');
    }

    public function test_authenticated_password_and_profile_updates_preserve_group_consistency(): void
    {
        $this->issue()->assertJsonPath('success', true); $this->verify()->assertJsonPath('success', true);
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->verify()->assertJsonPath('success', true);
        $buyer = User::where('user_type', 'buyer')->firstOrFail();
        $this->actingAs($buyer, 'api');
        $this->postJson('/api/user/update-password', ['current_password' => 'wrong123',
            'password' => 'changed123', 'password_confirmation' => 'changed123'])->assertJsonPath('success', false);
        $this->postJson('/api/user/update-password', ['current_password' => 'secret123',
            'password' => 'changed123', 'password_confirmation' => 'changed123'])->assertJsonPath('success', true);
        // This authentication fixture does not build unrelated social/video tables.
        $buyer->setRelation('isfollow', null)->setRelation('user_Favorites', collect())
            ->setRelation('followers', collect())->setRelation('wonAuctionItemsWithVideos', collect());
        $this->postJson('/api/user/update-profile', ['name' => 'Buyer Profile', 'email' => 'updated@example.com',
            'phone' => '+966588888888'])->assertOk()->assertJsonPath('success', true);
        $seller = User::where('user_type', 'seller')->firstOrFail();
        $this->assertTrue(Hash::check('changed123', $seller->password));
        $this->assertSame('updated@example.com', $seller->email);
        $this->assertSame('+966588888888', $seller->phone);
        $this->assertSame('Shared Owner', $seller->name);
        $this->assertSame('Buyer Profile', $buyer->fresh()->name);
    }

    public function test_existing_api_rate_limit_remains_enforced(): void
    {
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->verify(['otp' => '2222'])->assertOk()->assertJsonPath('success', false);
        }
        $this->verify()->assertStatus(429);
        $this->assertSame(0, User::count());
    }

    public function test_stale_historical_model_cannot_bypass_new_credential_association(): void
    {
        $stale = User::create(['name' => 'Original', 'user_name' => 'historic', 'phone' => '+966512345678',
            'email' => 'shared@example.com', 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'is_verified' => true]);
        $this->issue(['user_type' => 'seller', 'user_name' => 'shared_seller'])->assertJsonPath('success', true);
        $this->verify()->assertJsonPath('success', true);
        $this->assertNull($stale->credential_group_id);
        $stale->update(['password' => bcrypt('changed123')]);
        $this->assertTrue(Hash::check('changed123', User::where('user_type', 'seller')->firstOrFail()->password));
    }
}
