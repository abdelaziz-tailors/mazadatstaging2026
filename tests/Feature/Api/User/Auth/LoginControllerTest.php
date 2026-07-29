<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoginControllerTest extends TestCase
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

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'email' => 'user' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    public function test_login_succeeds_when_user_type_matches_exactly()
    {
        $user = $this->createUser(['user_type' => 'vendor']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'vendor',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user_type', 'vendor');
    }

    public function test_login_succeeds_when_buyer_vendor_user_logs_in_as_buyer()
    {
        $user = $this->createUser(['user_type' => 'buyer_vendor']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'buyer',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_login_succeeds_when_buyer_vendor_user_logs_in_as_vendor()
    {
        $user = $this->createUser(['user_type' => 'buyer_vendor']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'vendor',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_login_fails_when_user_type_does_not_match_registered_type()
    {
        $user = $this->createUser(['user_type' => 'buyer']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'vendor',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_login_fails_when_seller_type_sent_for_a_buyer_vendor_user()
    {
        $user = $this->createUser(['user_type' => 'buyer_vendor']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'seller',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_login_fails_with_validation_error_when_user_type_is_missing()
    {
        $user = $this->createUser();

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_login_fails_with_validation_error_when_user_type_is_invalid()
    {
        $user = $this->createUser();

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'not_a_real_type',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_login_fails_with_validation_error_when_phone_and_password_are_missing()
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'user_type' => 'buyer',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_login_fails_when_password_is_wrong()
    {
        $user = $this->createUser(['user_type' => 'buyer']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'wrong-password',
            'user_type' => 'buyer',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_login_succeeds_with_email_and_password()
    {
        $user = $this->createUser(['user_type' => 'buyer']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->email,
            'password' => 'secret123',
            'user_type' => 'buyer',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_fails_with_wrong_password_using_email()
    {
        $user = $this->createUser(['user_type' => 'buyer']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->email,
            'password' => 'wrong-password',
            'user_type' => 'buyer',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_login_fails_when_email_belongs_to_no_user()
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => 'nobody@example.com',
            'password' => 'secret123',
            'user_type' => 'buyer',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_login_succeeds_with_email_and_password_for_a_vendor_account()
    {
        $user = $this->createUser(['user_type' => 'vendor']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->email,
            'password' => 'secret123',
            'user_type' => 'vendor',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user_type', 'vendor');
    }

    public function test_login_succeeds_with_phone_and_password_for_a_vendor_account()
    {
        $user = $this->createUser(['user_type' => 'vendor']);

        $response = $this->withHeaders($this->headers())->postJson('/api/user/login', [
            'phone' => $user->phone,
            'password' => 'secret123',
            'user_type' => 'vendor',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }
}
