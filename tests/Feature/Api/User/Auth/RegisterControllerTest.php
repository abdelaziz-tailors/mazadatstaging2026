<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\City;
use App\Models\User\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Covers the "city" field added to the mobile app's standard registration
 * flow (POST /api/user/register -> POST /api/user/verify-account) — the
 * request accepts a `city` value (a `cities.id`) and it ends up stored as
 * `users.city_id` once the account is verified. The value is staged on the
 * `user_otps` row (as `city_id`) between the two requests, same as every
 * other registration field (name/email/phone/etc).
 *
 * Scope: only the phone/password registration flow — the Google
 * social-login/social-register endpoints were explicitly excluded.
 */
class RegisterControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'en',
        ];
    }

    private function createCity(array $overrides = []): City
    {
        return City::create(array_merge([
            'name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']),
            'is_active' => 1,
        ], $overrides));
    }

    private function registerPayload(array $overrides = []): array
    {
        $suffix = random_int(100000, 999999);

        return array_merge([
            'name' => 'Test User',
            'user_name' => 'user' . $suffix,
            'email' => 'user' . $suffix . '@example.com',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], $overrides);
    }

    private function verify(string $phone): \Illuminate\Testing\TestResponse
    {
        $otp = UserOtp::where('phone', $phone)->where('type', 'register')->first();

        return $this->withHeaders($this->headers())->postJson('/api/user/verify-account', [
            'phone' => $phone,
            'otp' => $otp->otp,
        ]);
    }

    public function test_register_accepts_a_valid_city_and_stages_it_on_the_pending_otp_record()
    {
        $city = $this->createCity();
        $payload = $this->registerPayload(['city' => $city->id]);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/register', $payload);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $otp = UserOtp::where('phone', $payload['phone'])->where('type', 'register')->first();
        $this->assertNotNull($otp);
        $this->assertEquals($city->id, $otp->city_id);
    }

    public function test_register_succeeds_without_a_city_and_stages_a_null_city_id()
    {
        $payload = $this->registerPayload();

        $response = $this->withHeaders($this->headers())->postJson('/api/user/register', $payload);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $otp = UserOtp::where('phone', $payload['phone'])->where('type', 'register')->first();
        $this->assertNotNull($otp);
        $this->assertNull($otp->city_id);
    }

    public function test_register_rejects_a_city_that_does_not_exist()
    {
        $payload = $this->registerPayload(['city' => 999999]);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/register', $payload);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('user_otps', ['phone' => $payload['phone']]);
    }

    public function test_verify_account_creates_the_user_with_the_city_carried_over_from_registration()
    {
        $city = $this->createCity();
        $payload = $this->registerPayload(['city' => $city->id]);
        $this->withHeaders($this->headers())->postJson('/api/user/register', $payload);

        $response = $this->verify($payload['phone']);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $user = User::where('phone', $payload['phone'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($city->id, $user->city_id);
    }

    public function test_verify_account_creates_the_user_with_a_null_city_when_none_was_provided()
    {
        $payload = $this->registerPayload();
        $this->withHeaders($this->headers())->postJson('/api/user/register', $payload);

        $response = $this->verify($payload['phone']);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $user = User::where('phone', $payload['phone'])->first();
        $this->assertNotNull($user);
        $this->assertNull($user->city_id);
    }
}
