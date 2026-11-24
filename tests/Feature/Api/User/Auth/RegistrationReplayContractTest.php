<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\User\User;
use App\Models\UserOtp;

/** Acceptance gates for exact registration challenge binding. */
class RegistrationReplayContractTest extends OptionalRegistrationEmailTest
{
    private function issue(array $overrides = []): array
    {
        return $this->postJson('/api/user/register', array_merge([
            'name' => 'Challenge Owner', 'user_name' => 'challenge_owner',
            'phone' => '0512345678', 'password' => 'secret123',
            'password_confirmation' => 'secret123', 'user_type' => 'buyer',
        ], $overrides))->assertJsonPath('success', true)->json('data');
    }

    private function verify(array $challenge, array $overrides = [])
    {
        return $this->postJson('/api/user/verify-account', array_merge([
            'phone' => $challenge['phone'], 'challenge_id' => $challenge['challenge_id'], 'otp' => '1111',
        ], $overrides));
    }

    public function test_token_is_256_bits_stored_only_as_hash_and_hidden_from_serialization(): void
    {
        $data = $this->issue();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $data['challenge_id']);
        $this->assertArrayNotHasKey('otp', $data);
        $pending = UserOtp::firstOrFail();
        $this->assertSame(hash('sha256', $data['challenge_id']), $pending->registration_challenge_hash);
        $this->assertStringNotContainsString($data['challenge_id'], json_encode($pending->getRawOriginal()));
        $this->assertArrayNotHasKey('registration_challenge_hash', $pending->toArray());
        $this->assertSame('1111', $pending->otp);
        $this->assertTrue($pending->expire_at->equalTo($pending->created_at->addMinutes(10)));
    }

    /** @dataProvider invalidReferences */
    public function test_invalid_references_leave_pending_registration_unchanged($reference): void
    {
        $data = $this->issue();
        $pending = UserOtp::firstOrFail();
        $before = $pending->getRawOriginal();
        $payload = ['phone' => $data['phone'], 'otp' => '1111'];
        if ($reference !== null) { $payload['challenge_id'] = $reference; }
        $this->postJson('/api/user/verify-account', $payload)->assertJsonPath('success', false);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->assertSame(0, User::count());
    }

    public static function invalidReferences(): array
    {
        return [[null], [''], ['1'], [str_repeat('a', 64)], [str_repeat('A', 64)],
            [str_repeat('f', 65)], [['invalid']], [123]];
    }

    public function test_cross_phone_type_workflow_expiry_and_wrong_code_do_not_consume(): void
    {
        $data = $this->issue();
        $pending = UserOtp::firstOrFail();
        foreach ([['phone' => '0599999999'], ['user_type' => 'seller'], ['otp' => '2222']] as $override) {
            $before = $pending->fresh()->getRawOriginal();
            $this->verify($data, $override)->assertJsonPath('success', false);
            $this->assertSame($before, $pending->fresh()->getRawOriginal());
        }
        $pending->update(['type' => 'role_access']);
        $before = $pending->fresh()->getRawOriginal();
        $this->verify($data)->assertJsonPath('success', false);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $pending->update(['type' => 'register', 'expire_at' => now()]);
        $before = $pending->fresh()->getRawOriginal();
        $this->verify($data)->assertStatus(403)->assertJsonPath('success', false);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->assertSame(0, User::count());
    }

    public function test_same_type_refresh_rotates_reference_and_rejects_old_request(): void
    {
        $old = $this->issue();
        $id = UserOtp::firstOrFail()->id;
        $latest = $this->issue(['phone' => '00966512345678', 'name' => 'Refreshed Owner']);
        $this->assertNotSame($old['challenge_id'], $latest['challenge_id']);
        $pending = UserOtp::firstOrFail();
        $this->assertSame($id, $pending->id);
        $before = $pending->getRawOriginal();
        $this->verify($old)->assertJsonPath('success', false);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->verify($latest)->assertJsonPath('success', true)->assertJsonPath('data.name', 'Refreshed Owner');
        $this->verify($latest)->assertJsonPath('success', false);
        $this->verify($old)->assertJsonPath('success', false);
        $this->assertSame(1, User::count());
        // A consumed same-type account is reserved; it cannot acquire another challenge.
        $this->postJson('/api/user/register', ['name' => 'Again Owner', 'user_name' => 'again',
            'phone' => '0512345678', 'password' => 'secret123', 'password_confirmation' => 'secret123',
            'user_type' => 'buyer'])->assertStatus(422);
        $this->verify($old)->assertJsonPath('success', false);
    }

    public function test_pre_release_null_challenge_is_preserved_and_requires_reissue(): void
    {
        $data = $this->issue();
        $pending = UserOtp::firstOrFail();
        $pending->update(['registration_challenge_hash' => null]);
        $before = $pending->fresh()->getRawOriginal();
        $this->verify($data)->assertJsonPath('success', false);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $latest = $this->issue();
        $this->assertSame($pending->id, UserOtp::firstOrFail()->id);
        $this->verify($latest)->assertJsonPath('success', true);
    }

    public function test_simultaneous_sibling_challenges_require_no_account_type_to_verify(): void
    {
        $buyer = $this->issue();
        $seller = $this->issue(['user_type' => 'seller', 'user_name' => 'seller_owner']);
        $this->assertNotSame($buyer['challenge_id'], $seller['challenge_id']);
        $this->verify($buyer)->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'buyer');
        $this->verify($buyer)->assertJsonPath('success', false);
        $this->assertSame(1, UserOtp::count());
        $this->verify($seller)->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'seller');
        $this->assertSame(2, User::count());
    }

    public function test_consumed_buyer_request_cannot_verify_later_seller_challenge(): void
    {
        $payload = ['name' => 'Replay Owner', 'phone' => '0512345678',
            'password' => 'secret123', 'password_confirmation' => 'secret123'];
        $this->postJson('/api/user/register', array_merge($payload, [
            'user_type' => 'buyer', 'user_name' => 'replay_buyer', 'email' => null,
        ]))->assertJsonPath('success', true);
        $verification = ['phone' => '0512345678', 'otp' => '1111',
            'challenge_id' => $this->registrationChallengeId('buyer')];
        $this->postJson('/api/user/verify-account', $verification)
            ->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'buyer');
        $this->postJson('/api/user/register', array_merge($payload, [
            'user_type' => 'seller', 'user_name' => 'replay_seller', 'email' => null,
        ]))->assertJsonPath('success', true);
        $seller = \App\Models\UserOtp::where('user_type', 'seller')->firstOrFail();
        $before = $seller->getRawOriginal();

        // Exact replay of the consumed Buyer request must not create/authenticate Seller.
        $response = $this->postJson('/api/user/verify-account', $verification);
        $this->assertSame(0, User::where('user_type', 'seller')->count(),
            'A consumed Buyer request created Seller; response type: '.$response->json('data.user_type'));
        $response->assertJsonPath('success', false);
        $this->assertSame($before, $seller->fresh()->getRawOriginal());
        $this->postJson('/api/user/verify-account', array_merge($verification, [
            'challenge_id' => $this->registrationChallengeId('seller'),
        ]))->assertJsonPath('success', true)->assertJsonPath('data.user_type', 'seller');
        $this->assertSame(2, User::count());
    }
}
