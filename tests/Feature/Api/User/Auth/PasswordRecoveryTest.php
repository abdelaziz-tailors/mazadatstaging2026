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
        $user = $this->user();
        $code = null;
        $this->mock(SmsService::class, function ($mock) use (&$code) {
            $mock->shouldReceive('sendWhatsappOtp')->once()->withArgs(function ($phone, $otp) use (&$code) {
                $code = $otp;
                return $phone === '+966512345678' && preg_match('/^[0-9]{4}$/D', $otp);
            })->andReturn(['success' => true]);
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
                ->with('01012345678', \Mockery::type('string'))->andReturn(['success' => true]);
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
        config(['services.infobip.base_url' => 'https://whatsapp.example.test',
            'services.infobip.api_key' => 'fake-key', 'services.infobip.whatsapp_from' => '966500000000',
            'services.infobip.whatsapp_template' => 'otp-template', 'services.infobip.whatsapp_lang' => 'en']);
        // Laravel 8 appends fake callbacks; replace the default fail-closed factory.
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(function ($request) use ($body, $httpStatus) {
            if ($request->url() !== 'https://whatsapp.example.test/whatsapp/1/message/template') {
                throw new \RuntimeException('Unexpected HTTP request');
            }
            return Http::response($body, $httpStatus);
        });
        $this->postJson('/api/user/forgot-password', ['phone' => '0512345678'])
            ->assertJsonPath('success', $accepted);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://whatsapp.example.test/whatsapp/1/message/template'
                && $request->hasHeader('Authorization', 'App fake-key')
                && $request['messages'][0]['to'] === '966512345678'
                && $request['messages'][0]['content']['templateName'] === 'otp-template'
                && preg_match('/^[0-9]{4}$/D', $request['messages'][0]['content']['templateData']['body']['placeholders'][0]);
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
        $message = fn ($group) => ['messages' => [['messageId' => 'fake-id', 'status' => ['groupName' => $group]]]];
        return [
            'pending' => [$message('PENDING'), 200, true],
            'delivered' => [$message('DELIVERED'), 200, true],
            'rejected despite HTTP 200' => [$message('REJECTED'), 200, false],
            'expired' => [$message('EXPIRED'), 200, false],
            'undeliverable' => [$message('UNDELIVERABLE'), 200, false],
            'missing message' => [[], 200, false],
            'missing message ID' => [['messages' => [['status' => ['groupName' => 'PENDING']]]], 200, false],
            'HTTP failure' => [[], 401, false],
        ];
    }

    public function test_unconfigured_provider_sends_nothing_and_invalidates_code(): void
    {
        $user = $this->user();
        config(['services.infobip.api_key' => null]);
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
}
