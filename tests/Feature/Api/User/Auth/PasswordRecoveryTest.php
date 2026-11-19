<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\User\User;
use App\Services\SmsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.otp.password_recovery_testing' => false]);
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array', 'app.debug' => false]);
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');
        app('laravellocalization')->setSupportedLocales(['en' => ['name' => 'English', 'native' => 'English']]);
        app()->setLocale('en');
        Http::fake(function () {
            throw new \RuntimeException('Unexpected HTTP request');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('password');
            $table->string('image')->default('');
            $table->boolean('is_verified')->default(true);
            $table->string('password_otp')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge(['phone' => '+966512345678', 'password' => bcrypt('old-password'),
            'password_otp' => '1234', 'expire_at' => now()->addMinutes(10)], $attributes));
    }

    private function reset(array $attributes = [])
    {
        return $this->postJson('/api/user/reset-password', array_merge([
            'phone' => '0512345678', 'otp' => '1234', 'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], $attributes));
    }

    public function test_local_phone_receives_otp_and_reset_is_one_time(): void
    {
        config(['services.otp.password_recovery_testing' => true]);
        $user = $this->user();
        $code = '1111';
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldNotReceive('sendWhatsappOtp');
        });
        $this->postJson('/api/user/forgot-password', ['phone' => '0512345678'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonMissing(['otp' => $code]);
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturn(new PersonalAccessTokenResult('test-token', new Token()));
        });
        $this->reset(['otp' => $code])->assertOk()->assertJsonPath('data.token', 'test-token');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertNull($user->fresh()->password_otp);
        $this->reset(['otp' => $code])->assertJsonPath('success', false);
    }

    /** @dataProvider reusedPasswordMessages */
    public function test_current_password_is_rejected_without_consuming_otp(string $locale, string $message): void
    {
        app('laravellocalization')->setSupportedLocales([
            'en' => ['name' => 'English', 'native' => 'English'],
            'ar' => ['name' => 'Arabic', 'native' => 'العربية'],
        ]);
        app()->setLocale($locale);
        $user = $this->user();
        $originalPassword = $user->password;
        $originalExpiry = $user->expire_at;
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldNotReceive('make');
        });

        $this->reset(['password' => 'old-password', 'password_confirmation' => 'old-password'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonPath('message', $message);

        $user->refresh();
        $this->assertSame($originalPassword, $user->password);
        $this->assertSame('1234', $user->password_otp);
        $this->assertEquals($originalExpiry, $user->expire_at);
    }

    public static function reusedPasswordMessages(): array
    {
        return [
            'English' => ['en', 'The new password must be different from your current password.'],
            'Arabic' => ['ar', 'يجب أن تكون كلمة المرور الجديدة مختلفة عن كلمة المرور الحالية.'],
        ];
    }

    public function test_token_failure_rolls_back_password_and_otp_consumption(): void
    {
        $user = $this->user();
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id')->primary();
        });
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturnUsing(function () {
                $this->assertGreaterThan(0, DB::transactionLevel());
                DB::table('oauth_access_tokens')->insert(['id' => 'partial-token']);
                throw new \RuntimeException('token unavailable');
            });
        });
        $this->reset()->assertStatus(500);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertSame('1234', $user->fresh()->password_otp);
        $this->assertSame(0, DB::table('oauth_access_tokens')->count());
    }

    public function test_expired_wrong_and_unverified_codes_cannot_reset_password(): void
    {
        $user = $this->user();
        $this->reset(['otp' => '9999'])->assertJsonPath('success', false);
        $user->update(['expire_at' => now()->subSecond()]);
        $this->reset()->assertStatus(403);
        $user->update(['expire_at' => null]);
        $this->reset()->assertStatus(403);
        $user->update(['expire_at' => now()->addMinute(), 'is_verified' => false]);
        $this->reset()->assertJsonPath('success', false);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_provider_failure_invalidates_otp(): void
    {
        $user = $this->user();
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('sendWhatsappOtp')->once()->andReturn(['success' => false]);
        });
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])
            ->assertJsonPath('success', false);
        $this->assertNull($user->fresh()->password_otp);
        $this->assertNull($user->fresh()->expire_at);
    }

    public function test_exact_legacy_phone_is_preserved_and_resend_replaces_code(): void
    {
        $user = $this->user(['phone' => '01012345678']);
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('sendWhatsappOtp')->once()
                ->with('01012345678', \Mockery::type('string'), \Mockery::type(\DateTimeInterface::class))->andReturn(['success' => true]);
        });
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])
            ->assertJsonPath('success', true)->assertJsonPath('data.phone', $user->phone);
        $this->assertNotSame('1234', $user->fresh()->password_otp);
        $this->reset(['phone' => $user->phone])->assertJsonPath('success', false);
    }

    public function test_code_is_invalid_at_exact_expiry(): void
    {
        $this->travelTo(now()->startOfSecond());
        $this->user(['expire_at' => now()]);
        $this->reset()->assertStatus(403);
        $this->travelBack();
    }

    public function test_validation_and_unknown_phone_are_rejected(): void
    {
        $this->postJson('/api/user/forgot-password', [])->assertStatus(422);
        $this->postJson('/api/user/forgot-password', ['phone' => ['invalid']])->assertStatus(422);
        $this->postJson('/api/user/forgot-password', ['phone' => 'unknown'])->assertJsonPath('success', false);
        $this->reset(['otp' => ''])->assertStatus(422);
        $this->reset(['password_confirmation' => 'different'])->assertStatus(422);
    }

    public function test_ambiguous_normalized_phone_is_not_selected(): void
    {
        $this->user();
        $this->user(['phone' => '512345678']);
        $this->postJson('/api/user/forgot-password', ['phone' => '00966512345678'])
            ->assertJsonPath('success', false);
    }

    /** @dataProvider phoneVariants */
    public function test_phone_variants_resolve_the_same_account(string $phone): void
    {
        $user = $this->user(['expire_at' => now()->subMinute()]);
        // Expiry is only reached if both phone and OTP matched the account.
        $this->reset(['phone' => $phone])->assertStatus(403);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public static function phoneVariants(): array
    {
        return array_map(fn ($phone) => [$phone], [
            '+966512345678', '966512345678', '00966512345678',
            '0512345678', '512345678', '+966 (51) 234-5678',
        ]);
    }

    /** @dataProvider whatsappResponses */
    public function test_whatsapp_response_controls_recovery_result(array $body, int $httpStatus, bool $accepted): void
    {
        $user = $this->user();
        config(['services.whatsapp_sender.base_url' => 'https://whatsapp.example.test',
            'services.whatsapp_sender.api_token' => 'fake-key']);
        // Laravel 8 appends fake callbacks; replace the default fail-closed factory.
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(function ($request) use ($body, $httpStatus) {
            if ($request->url() !== 'https://whatsapp.example.test/api/external/messages/send') {
                throw new \RuntimeException('Unexpected HTTP request');
            }
            return Http::response($body, $httpStatus);
        });
        $this->postJson('/api/user/forgot-password', ['phone' => '0512345678'])
            ->assertJsonPath('success', $accepted);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://whatsapp.example.test/api/external/messages/send'
                && $request->hasHeader('X-API-Token', 'fake-key')
                && $request['phone'] === '+966512345678'
                && preg_match('/\*[0-9]{4}\*/', $request['message']);
        });
        Http::assertSentCount(1);
        if ($accepted) {
            $this->assertNotNull($user->fresh()->password_otp);
        } else {
            $this->assertNull($user->fresh()->password_otp);
            $this->assertNull($user->fresh()->expire_at);
        }
    }

    public static function whatsappResponses(): array
    {
        return [
            'explicit success' => [['success' => true], 200, true],
            'confirmed pending response' => [['success' => true, 'messageId' => 3358,
                'status' => 'pending', 'pointsCharged' => 1, 'remainingPoints' => 1419], 200, true],
            'apparent rejection' => [['success' => false], 200, false],
            'queued remains unverified' => [['status' => 'queued'], 202, false],
            'missing message' => [[], 200, false],
            'HTTP failure' => [[], 401, false],
            'success cannot override HTTP failure' => [['success' => true], 500, false],
        ];
    }

    public function test_unconfigured_provider_sends_nothing_and_invalidates_code(): void
    {
        $user = $this->user();
        config(['services.whatsapp_sender.api_token' => null]);
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])->assertJsonPath('success', false);
        Http::assertNothingSent();
        $this->assertNull($user->fresh()->password_otp);
    }

    public function test_api_rate_limit_remains_enforced(): void
    {
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->postJson('/api/user/forgot-password', ['phone' => 'unknown'])->assertOk();
        }
        $this->postJson('/api/user/forgot-password', ['phone' => 'unknown'])->assertStatus(429);
        Http::assertNothingSent();
    }

    public function test_leading_zero_otp_is_preserved_and_cannot_be_reused(): void
    {
        $user = $this->user(['password_otp' => '0123']);
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturn(new PersonalAccessTokenResult('test-token', new Token()));
        });
        $this->reset(['otp' => '123'])->assertStatus(422);
        $this->reset(['otp' => '0123'])->assertJsonPath('success', true);
        $this->reset(['otp' => '0123'])->assertJsonPath('success', false);
        $this->assertNull($user->fresh()->expire_at);
    }

    public function test_recovery_persists_ten_minute_expiry_before_http_without_holding_transaction(): void
    {
        $this->travelTo(now()->startOfSecond());
        $user = $this->user();
        config(['services.whatsapp_sender.base_url' => 'https://sender.example.test',
            'services.whatsapp_sender.api_token' => 'fake-key']);
        Http::swap(new \Illuminate\Http\Client\Factory());
        $issued = null;
        Http::fake(function ($request) use ($user, &$issued) {
            $issued = $user->fresh();
            $this->assertSame(0, DB::transactionLevel());
            $this->assertTrue($issued->expire_at->equalTo(now()->addMinutes(10)));
            $this->assertStringContainsString('*'.$issued->password_otp.'*', $request['message']);
            $this->assertStringContainsString($issued->expire_at->copy()->utc()->format('Y-m-d H:i:s').' UTC', $request['message']);
            return Http::response('{}', 202);
        });
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])->assertJsonPath('success', false);
        $this->assertNotNull($issued);
        $this->assertNull($user->fresh()->password_otp);
        $this->assertNull($user->fresh()->expire_at);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        Http::assertSentCount(1);
        $this->travelBack();
    }

    public function test_recovery_failure_preserves_a_newer_resend(): void
    {
        $user = $this->user();
        $this->mock(SmsService::class, function ($mock) use ($user) {
            $mock->shouldReceive('sendWhatsappOtp')->once()->andReturnUsing(function ($phone, $code) use ($user) {
                $user->update(['password_otp' => $code === '9876' ? '9875' : '9876', 'expire_at' => now()->addMinutes(11)]);
                return ['success' => false];
            });
        });
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])->assertJsonPath('success', false);
        $this->assertNotNull($user->fresh()->password_otp);
        $this->assertNotNull($user->fresh()->expire_at);
    }

    public function test_recovery_service_exception_clears_new_code_without_leaking_exception(): void
    {
        $user = $this->user();
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('sendWhatsappOtp')->once()->andThrow(new \RuntimeException('SECRET'));
        });
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])
            ->assertJsonPath('success', false)->assertDontSee('SECRET');
        $this->assertNull($user->fresh()->password_otp);
    }

    public function test_production_ignores_password_recovery_test_mode_and_sanitizes_logs(): void
    {
        app()->instance('env', 'production');
        config(['services.otp.password_recovery_testing' => true]);
        $user = $this->user();
        \Illuminate\Support\Facades\Log::spy();
        $this->mock(SmsService::class, function ($mock) {
            $mock->shouldReceive('sendWhatsappOtp')->once()->andReturn([
                'success' => false, 'error' => 'SECRET', 'response' => 'SECRET', 'status' => 'SECRET',
            ]);
        });
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])
            ->assertJsonPath('success', false)->assertDontSee('SECRET');
        $this->assertNull($user->fresh()->password_otp);
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->with(
            'FORGET_PASSWORD_WHATSAPP_OTP_FAILED', ['http_status' => null, 'transport_success' => false]
        );
    }

    public function test_confirmed_provider_acceptance_still_allows_password_reset(): void
    {
        $user = $this->user();
        config(['services.whatsapp_sender.base_url' => 'https://sender.example.test',
            'services.whatsapp_sender.api_token' => 'fake-key']);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(fn () => Http::response(['success' => true, 'messageId' => 3358,
            'status' => 'pending', 'pointsCharged' => 1, 'remainingPoints' => 1419]));
        \Illuminate\Support\Facades\Log::spy();
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['phone', 'expire_at']])
            ->assertJsonCount(2, 'data'); // No OTP or provider metadata is exposed.
        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')->once()->with('FORGET_PASSWORD_WHATSAPP_OTP_ACCEPTED');
        Http::assertSentCount(1);
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturn(new PersonalAccessTokenResult('test-token', new Token()));
        });
        $this->reset(['otp' => $user->fresh()->password_otp])->assertJsonPath('success', true);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_unverified_account_cannot_request_recovery_delivery(): void
    {
        $user = $this->user(['is_verified' => false]);
        $this->postJson('/api/user/forgot-password', ['phone' => $user->phone])->assertJsonPath('success', false);
        $this->assertSame('1234', $user->fresh()->password_otp);
        Http::assertNothingSent();
    }
}
