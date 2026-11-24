<?php

namespace Tests\Feature\Api\User\Auth;

use App\Models\User\User;
use App\Models\UserOtp;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;
use Tests\TestCase;

class OptionalRegistrationEmailTest extends TestCase
{
    protected array $registrationChallengeIds = [];

    protected function registrationChallengeId(?string $type = null): string
    {
        return $type === null ? (end($this->registrationChallengeIds) ?: str_repeat('0', 64))
            : ($this->registrationChallengeIds[$type] ?? str_repeat('0', 64));
    }

    public function test_static_otp_workflow_activation_isolation_and_reuse(): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => []]);
        $this->travelTo(now()->startOfSecond());
        \Illuminate\Support\Facades\Http::fake();
        $unrelated = User::create(['phone' => '+966599999999', 'user_name' => 'unrelated',
            'password' => bcrypt('secret123'), 'is_verified' => false]);
        $before = $unrelated->fresh()->getRawOriginal();
        $response = $this->post('/api/user/register', $this->payload(['user_type' => 'buyer']))
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('code', 200);
        $this->assertArrayNotHasKey('otp', $response->json('data'));
        $pending = UserOtp::firstOrFail();
        $this->assertSame('1111', $pending->otp);
        $this->assertSame('+966512345678', $pending->phone);
        $this->assertFalse($pending->is_verified);
        $this->assertTrue($pending->expire_at->equalTo(now()->addMinutes(10)));
        $this->assertSame(1, User::count());
        // Fixed-code registration intentionally bypasses the delivery service.
        \Illuminate\Support\Facades\Http::assertNothingSent();
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => '+966599999999', 'otp' => '1111'])
            ->assertJsonPath('success', false);
        $this->assertSame($before, $unrelated->fresh()->getRawOriginal());
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => '0512345678', 'otp' => '1111'])
            ->assertOk()->assertJsonPath('code', 200)->assertJsonPath('success', true);
        $user = User::where('phone', $pending->phone)->firstOrFail();
        $this->assertTrue((bool) $user->is_verified);
        $this->assertTrue((bool) $user->is_active);
        $this->assertNull($user->otp);
        $this->assertNull($user->expire_at);
        $consumed = UserOtp::withTrashed()->findOrFail($pending->id);
        $this->assertTrue($consumed->is_verified);
        $this->assertTrue($consumed->trashed());
        $this->assertNull($consumed->otp);
        $this->assertNull($consumed->expire_at);
        $verifiedState = $user->getRawOriginal();
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $user->phone, 'otp' => '1111'])
                ->assertJsonPath('success', false);
        }
        $this->assertSame($verifiedState, $user->fresh()->getRawOriginal());
        $this->assertSame($before, $unrelated->fresh()->getRawOriginal());
        $this->assertSame(2, User::count());
        $this->travelBack();
    }

    /** @dataProvider invalidStaticCodes */
    public function test_static_otp_invalid_inputs_leave_pending_account_unchanged(array $input): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => []]);
        $this->post('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $pending = UserOtp::firstOrFail();
        $before = $pending->fresh()->getRawOriginal();
        $response = $this->postJson('/api/user/verify-account', array_merge(['challenge_id' => $this->registrationChallengeId(), 'phone' => $pending->phone], $input))
            ->assertOk()->assertJsonPath('success', false)->assertJsonPath('code', 200);
        $this->assertArrayNotHasKey('exception', $response->json());
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->assertSame(0, User::count());
    }

    public static function invalidStaticCodes(): array
    {
        return [[['otp' => '2222']], [['otp' => '0000']], [['otp' => '']], [[]],
            [['otp' => 'abcd']], [['otp' => '11111']], [['otp' => ['1111']]],
            [['otp' => '1111', 'phone' => '+966588888888']], [['otp' => '1111', 'phone' => '']]];
    }

    public function test_static_otp_expiry_and_repeated_expired_requests_do_not_activate(): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => []]);
        $this->travelTo(now()->startOfSecond());
        $this->post('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $pending = UserOtp::firstOrFail();
        $before = $pending->fresh()->getRawOriginal();
        $this->travelTo($pending->expire_at);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $pending->phone, 'otp' => '1111'])
                ->assertStatus(403)->assertJsonPath('code', 403)->assertJsonPath('success', false);
        }
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->assertSame(0, User::count());
        $this->travelBack();
    }

    /** @dataProvider registrationWhatsappResponses */
    public function test_static_otp_whatsapp_adapter_and_registration_delivery(array $body, int $status, bool $accepted, bool $timeout): void
    {
        config(['services.infobip.base_url' => 'https://whatsapp.example.test',
            'services.infobip.api_key' => 'isolated-fake-key', 'services.infobip.whatsapp_from' => 'fake-sender',
            'services.infobip.whatsapp_template' => 'fake-template', 'services.infobip.whatsapp_lang' => 'en']);
        $service = new \App\Services\SmsService();
        app()->instance(\App\Services\SmsService::class, $service);
        $diagnostics = [];
        $nullLogger = \Illuminate\Support\Facades\Log::channel('null');
        \Illuminate\Support\Facades\Log::shouldReceive('channel')->andReturn($nullLogger);
        \Illuminate\Support\Facades\Log::shouldReceive('error')->andReturnUsing(function ($event, $context = []) use (&$diagnostics) {
            $diagnostics[] = [$event, $context];
        });
        \Illuminate\Support\Facades\Log::shouldReceive('warning')->andReturnUsing(function ($event) use (&$diagnostics) {
            $diagnostics[] = [$event];
        });
        $requests = [];
        \Illuminate\Support\Facades\Http::swap(new \Illuminate\Http\Client\Factory());
        \Illuminate\Support\Facades\Http::fake(function ($request) use ($body, $status, $timeout, &$requests) {
            $this->assertSame('https://whatsapp.example.test/whatsapp/1/message/template', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertTrue($request->hasHeader('Authorization', 'App isolated-fake-key'));
            $this->assertSame('966512345678', $request['messages'][0]['to']);
            $this->assertSame('fake-template', $request['messages'][0]['content']['templateName']);
            $requests[] = $request->data();
            if ($timeout) {
                throw new \Illuminate\Http\Client\ConnectionException('simulated timeout');
            }
            return \Illuminate\Support\Facades\Http::response($body, $status);
        });
        // Exercise the real adapter using the actual fixed code staged by registration.
        $this->post('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $pending = UserOtp::firstOrFail();
        $this->assertSame('1111', $pending->otp);
        $result = $service->sendRegistrationOtp($pending->phone, $pending->otp);
        $this->assertSame($accepted, $result['success']);
        $this->assertSame(['1111'], $requests[0]['messages'][0]['content']['templateData']['body']['placeholders']);

        // Registration must bypass the provider in production too.
        app()->instance('env', 'production');
        $this->post('/api/user/register', $this->payload())->assertOk()->assertJsonPath('success', true);
        $this->assertCount(1, $requests);
        $this->assertSame('1111', $pending->fresh()->otp);
        $this->assertSame(0, User::count());
    }

    public static function registrationWhatsappResponses(): array
    {
        return [
            'accepted' => [['messages' => [['messageId' => 'fake-id', 'status' => ['groupName' => 'PENDING']]]], 200, true, false],
            'http400' => [[], 400, false, false],
            'http500' => [[], 500, false, false],
            'timeout' => [[], 200, false, true],
            'invalid_response' => [['unexpected' => true], 200, false, false],
            'provider_rejected' => [['messages' => [['messageId' => 'fake-id', 'status' => ['groupName' => 'REJECTED']]]], 200, false, false],
        ];
    }

    public function test_production_registration_ignores_client_and_legacy_test_flags(): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => [], 'services.otp.testing' => false,
            'services.otp.test_code' => '9999']);
        \Illuminate\Support\Facades\Queue::fake();
        $this->test_static_otp_workflow_activation_isolation_and_reuse();
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
    }

    /** @dataProvider phoneDeliveryPolicies */
    public function test_global_phone_challenge_preserves_delivery_and_single_use($environment, $qa, $deliver, $accepted): void
    {
        app()->instance('env', $environment);
        config(['services.otp.qa_phones' => $qa]);
        $this->travelTo(now()->startOfSecond());
        $user = User::create(['phone' => '+966512345678', 'user_name' => 'phone-owner', 'password' => bcrypt('secret123')]);
        $other = User::create(['phone' => '+966599999999', 'user_name' => 'other']);
        $before = $other->fresh()->getRawOriginal();
        $this->mock(\App\Services\SmsService::class, function ($mock) use ($deliver, $accepted) {
            $mock->shouldNotReceive('sendRegistrationOtp');
            if ($deliver) {
                $mock->shouldReceive('sendWhatsappOtp')->once()->with('+966512345678', '1111')
                    ->andReturn(['success' => $accepted]);
            } else {
                $mock->shouldNotReceive('sendWhatsappOtp');
            }
        });
        $controller = app(\App\Http\Controllers\api\User\Auth\SendOtpController::class);
        $request = \App\Http\Requests\api\User\Auth\SendOtpRequest::create('/', 'POST', ['phone' => '0512345678']);
        $this->assertSame($accepted, $controller($request)->getData(true)['success']);
        $verify = function ($otp) use ($controller) {
            return $controller->approvedMobile(\App\Http\Requests\api\User\Auth\SendPhoneOtpRequest::create('/', 'POST', ['phone' => '0512345678', 'otp' => $otp]));
        };
        if ($accepted) {
            $this->assertSame('1111', $user->fresh()->otp);
            $this->assertTrue($user->fresh()->expire_at->equalTo(now()->addMinutes(10)));
            foreach (['2222', '', null, 'abc', ['1111']] as $invalid) {
                $this->assertSame(422, $verify($invalid)->status());
            }
            $this->assertFalse((bool) $user->fresh()->is_verified);
            $user->update(['expire_at' => now()]);
            $this->assertSame(403, $verify('1111')->status());
            $user->update(['expire_at' => now()->addMinutes(10)]);
            $this->assertTrue($verify('1111')->getData(true)['success']);
            $this->assertTrue((bool) $user->fresh()->is_verified);
            $this->assertSame(422, $verify('1111')->status());
        } else {
            $this->assertFalse((bool) $user->fresh()->is_verified);
            $this->assertSame(422, $verify('1111')->status());
        }
        $this->assertNull($user->fresh()->otp);
        $this->assertNull($user->fresh()->expire_at);
        $this->assertSame($before, $other->fresh()->getRawOriginal());
        $this->travelBack();
    }

    public static function phoneDeliveryPolicies(): array
    {
        return [['production', [], true, true], ['production', [], true, false],
            ['production', ['0512345678'], false, true], ['local', [], false, true]];
    }

    public function test_registration_endpoint_does_not_fall_back_to_legacy_user_otp(): void
    {
        $user = User::create(['phone' => '+966512345678', 'user_name' => 'legacy',
            'otp' => '1111', 'password_otp' => '1111', 'expire_at' => now()->addMinutes(10)]);
        $before = $user->fresh()->getRawOriginal();
        $this->postJson('/api/user/verify-account', ['phone' => $user->phone, 'otp' => '1111'])
            ->assertJsonPath('success', false);
        $this->postJson('/api/user/verify-account', ['phone' => $user->phone, 'otp' => '1111',
            'challenge_id' => str_repeat('a', 64)])->assertJsonPath('success', false);
        $this->assertSame($before, $user->fresh()->getRawOriginal());
    }
    public function test_legacy_phone_resend_and_verification_consume_code_once(): void
    {
        $user = User::create(['phone' => '+966512345678', 'user_name' => 'legacy', 'password' => bcrypt('secret123')]);
        $controller = app(\App\Http\Controllers\api\User\Auth\SendOtpController::class);
        $issue = \App\Http\Requests\api\User\Auth\SendOtpRequest::create('/', 'POST', ['phone' => '0512345678']);
        $response = $controller($issue);
        $this->assertTrue($response->getData(true)['success']);
        $this->assertArrayNotHasKey('otp', $response->getData(true)['data']);
        $this->assertSame('1111', $user->fresh()->otp);
        $user->update(['expire_at' => now()]);
        $verify = \App\Http\Requests\api\User\Auth\SendPhoneOtpRequest::create('/', 'POST', ['phone' => '0512345678', 'otp' => '1111']);
        $this->assertSame(403, $controller->approvedMobile($verify)->status());
        $controller($issue);
        $this->assertTrue($controller->approvedMobile($verify)->getData(true)['success']);
        $this->assertNull($user->fresh()->otp);
        $this->assertNull($user->fresh()->expire_at);
        $this->assertSame(422, $controller->approvedMobile($verify)->status());
    }

    public function test_registration_rolls_back_creation_and_consumption_on_token_failure(): void
    {
        $this->postJson('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $pending = UserOtp::firstOrFail();
        $before = $pending->fresh()->getRawOriginal();
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andThrow(new \RuntimeException('token unavailable'));
        });
        $this->postJson('/api/user/verify-account', ['phone' => $pending->phone, 'otp' => '1111',
            'challenge_id' => $this->registrationChallengeId()])->assertStatus(500);
        $this->assertSame($before, $pending->fresh()->getRawOriginal());
        $this->assertSame(0, User::count());
    }
    public function test_production_qa_registration_uses_fixed_code(): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => ['0512345678']]);
        $this->postJson('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $this->assertSame('1111', UserOtp::firstOrFail()->otp);
    }

    public function test_registration_insert_failure_is_safe_and_rolls_back(): void
    {
        Schema::drop('user_otps');
        \Illuminate\Support\Facades\Log::shouldReceive('error')->once()->with('REGISTER_FAILED',
            \Mockery::on(fn ($context) => $context['exception_class'] === \Illuminate\Database\QueryException::class
                && array_keys($context) === ['exception_class', 'file', 'line', 'sqlstate']));
        $response = $this->postJson('/api/user/register', $this->payload())->assertStatus(500)
            ->assertJsonPath('code', 500)->assertJsonPath('success', false);
        $this->assertArrayNotHasKey('exception', $response->json());
        $this->assertArrayNotHasKey('trace', $response->json());
        $this->assertSame(0, User::count());
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_registration_form_fields_stage_a_hashed_password(): void
    {
        $this->post('/api/user/register', $this->payload(['user_type' => 'buyer']))
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('code', 200);
        $pending = UserOtp::firstOrFail();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret123', $pending->password));
        $this->assertSame(0, User::count());
    }

    /** @dataProvider invalidRegistrations */
    public function test_registration_validation_is_structured(array $overrides, string $field): void
    {
        $this->post('/api/user/register', $this->payload($overrides))->assertStatus(422)
            ->assertJsonPath('code', 422)->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => [$field]]);
        $this->assertSame(0, UserOtp::count());
        $this->assertSame(0, User::count());
    }

    public static function invalidRegistrations(): array
    {
        return [
            [['user_type' => 'admin'], 'user_type'],
            [['user_type' => 'partner'], 'user_type'],
            [['user_type' => 'unknown'], 'user_type'],
            [['name' => ''], 'name'],
            [['phone' => 'invalid'], 'phone'],
            [['password_confirmation' => 'different'], 'password'],
        ];
    }

    public function test_registration_duplicate_username_is_rejected(): void
    {
        User::create(['user_name' => 'testuser', 'phone' => '+966599999999']);
        $this->postJson('/api/user/register', $this->payload())->assertStatus(422)
            ->assertJsonPath('code', 422)->assertJsonStructure(['errors' => ['user_name']]);
        $this->assertSame(0, UserOtp::count());
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'services.otp.qa_phones' => [], 'app.dashboard_locale' => 'en', 'cache.default' => 'array',
            'logging.default' => 'null']);
        // Intercept every destination; tests exercising the adapter install their own complete fake.
        \Illuminate\Support\Facades\Http::swap(new \Illuminate\Http\Client\Factory());
        \Illuminate\Support\Facades\Http::fake(function () {
            throw new \RuntimeException('Unexpected HTTP request in isolated registration tests');
        });
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');
        app('laravellocalization')->setSupportedLocales(['en' => ['name' => 'English', 'native' => 'English']]);
        app()->setLocale('en');
        foreach (['users', 'user_otps', 'admins'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                foreach (['name', 'user_name', 'email', 'phone', 'national_id', 'user_type', 'password',
                    'commercial_register', 'otp', 'password_otp', 'type', 'city_id', 'admin_id', 'user_id'] as $field) {
                    if ($field === 'user_type') { $table->string($field)->default('buyer'); }
                    else { $table->string($field)->nullable(); }
                }
                $table->string('image')->default('');
                $table->string('remember_token')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('registration_phone_key')->virtualAs(\App\Helpers\MobileNumber::sqlKey('phone', 'sqlite'));
                $table->unique($name === 'user_otps' ? ['registration_phone_key', 'type'] : ['registration_phone_key'], $name.'_registration_phone_unique');
                if ($name !== 'user_otps') {
                    $table->string('registration_email_key')->virtualAs("CASE WHEN deleted_at IS NULL THEN NULLIF(LOWER(TRIM(email)), '') ELSE NULL END");
                    $table->unique('registration_email_key', $name.'_registration_email_unique');
                }
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('expire_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
                if ($name === 'user_otps') { $table->unique(['phone', 'type']); }
            });
        }
        (require database_path('migrations/2026_11_24_000001_scope_account_phone_uniqueness_by_type.php'))->up();
        (require database_path('migrations/2026_11_24_000002_add_registration_challenge_hash_to_user_otps.php'))->up();
        // Retain the response token, as a client would; never reconstruct it from storage.
        app('events')->listen(\Illuminate\Foundation\Http\Events\RequestHandled::class, function ($event) {
            if ($event->request->is('api/user/register')) {
                $data = json_decode($event->response->getContent(), true);
                if (isset($data['data']['challenge_id'])) {
                    $this->registrationChallengeIds[$event->request->input('user_type') ?: 'buyer'] = $data['data']['challenge_id'];
                }
            }
        });
        Schema::create('cities', function (Blueprint $table) { $table->id(); });
        DB::table('cities')->insert(['id' => 1]);
        require_once database_path('migrations/New folder/2022_10_16_092804_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        $this->mock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')->andReturn(new PersonalAccessTokenResult('test-token', new Token()));
        });
        $this->mock(\App\Services\SmsService::class, function ($mock) {
            $mock->shouldNotReceive('sendRegistrationOtp');
        });
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['name' => 'Test User', 'user_name' => 'testuser', 'phone' => '0512345678',
            'password' => 'secret123', 'password_confirmation' => 'secret123'], $overrides);
    }

    /** @dataProvider absentEmails */
    public function test_absent_email_is_null_through_otp_verification_and_login($email): void
    {
        // A previous email-free account must not block verification.
        User::create(['phone' => '+966599999999', 'user_name' => 'previous', 'email' => null]);
        $payload = $this->payload();
        if ($email !== 'omitted') { $payload['email'] = $email; }
        $this->postJson('/api/user/register', $payload)->assertOk()->assertJsonPath('data.phone', '+966512345678');
        $otp = UserOtp::first();
        $this->assertNull($otp->email);
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => '0512345678', 'otp' => $otp->otp])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.email', '-');
        $user = User::where('phone', '+966512345678')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertTrue((bool) $user->is_verified);
        $this->postJson('/api/user/login', ['phone' => '0512345678', 'password' => 'secret123', 'user_type' => 'buyer'])
            ->assertJsonPath('success', true)->assertJsonPath('data.token', 'test-token');
    }

    public static function absentEmails(): array
    {
        return [['omitted'], [null], [''], ['   ']];
    }

    /** @dataProvider invalidPhones */
    public function test_invalid_mobile_is_rejected($phone): void
    {
        $this->postJson('/api/user/register', $this->payload(['phone' => $phone]))
            ->assertStatus(422)->assertJsonPath('success', false);
        $this->assertSame(0, UserOtp::count());
    }

    public static function invalidPhones(): array
    {
        return [[null], [''], ['abc'], ['1e10'], ['123'], [['0512345678']], ['+966512345678123456'], ['+96612345678'], ['0096612345678'], ['96612345678'], ['+96651234567'], ['+9665123456789'], ['++966512345678']];
    }

    public function test_missing_mobile_and_invalid_or_duplicate_email_are_rejected(): void
    {
        $payload = $this->payload(); unset($payload['phone']);
        $this->postJson('/api/user/register', $payload)->assertStatus(422);
        $this->postJson('/api/user/register', $this->payload(['email' => 'bad']))->assertStatus(422);
        User::create(['phone' => '+966599999999', 'email' => 'used@example.com']);
        $this->postJson('/api/user/register', $this->payload(['email' => 'used@example.com']))->assertStatus(422);
    }

    /** @dataProvider phoneVariants */
    public function test_equivalent_existing_mobile_is_rejected(string $phone): void
    {
        User::create(['phone' => $phone]);
        $this->postJson('/api/user/register', $this->payload())->assertStatus(422);
    }

    public static function phoneVariants(): array
    {
        return [['+966512345678'], ['966512345678'], ['00966512345678'], ['0512345678'], ['512345678']];
    }

    public function test_provided_email_is_preserved_and_rechecked_at_verification(): void
    {
        $this->postJson('/api/user/register', $this->payload(['email' => 'new@example.com']))->assertJsonPath('success', true);
        $otp = UserOtp::first();
        $this->assertSame('new@example.com', $otp->email);
        User::create(['phone' => '+966599999999', 'email' => 'new@example.com']);
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => $otp->otp])->assertJsonPath('success', false);
        $this->assertSame(1, User::count());
    }

    /** @dataProvider accountTypes */
    public function test_email_free_verification_preserves_account_types(string $type): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => []]);
        $payload = $this->payload(['user_type' => $type]);
        if ($type === 'vendor') {
            $payload['commercial_register'] = \Illuminate\Http\UploadedFile::fake()->create('hotfix-test.pdf', 1, 'application/pdf');
        }
        $this->post('/api/user/register', $payload)->assertOk()->assertJsonPath('success', true);
        $otp = UserOtp::firstOrFail();
        $this->assertSame('1111', $otp->otp);
        $this->assertFalse($otp->is_verified);
        $this->assertSame(0, User::count());
        if ($otp->commercial_register) {
            // Only the file created by this test is removed.
            unlink(storage_path('app/public/vendor-commercial-files/'.basename($otp->commercial_register)));
        }
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => $otp->otp])
            ->assertJsonPath('success', true)->assertJsonPath('data.user_type', $type);
        $this->assertNull(User::first()->email);
        if ($type === 'vendor') {
            $this->assertDatabaseHas('admins', ['user_id' => User::first()->id, 'email' => null]);
        }
    }

    public static function accountTypes(): array
    {
        return [['buyer'], ['vendor'], ['buyer_vendor'], ['seller']];
    }

    public function test_resubmission_normalizes_to_same_pending_record_and_clears_email(): void
    {
        $this->postJson('/api/user/register', $this->payload(['email' => 'first@example.com']))->assertJsonPath('success', true);
        $this->postJson('/api/user/register', $this->payload(['phone' => '00966512345678']))->assertJsonPath('success', true);
        $this->assertSame(1, UserOtp::count());
        $this->assertNull(UserOtp::first()->email);
    }

    public function test_valid_email_survives_verification_and_email_login(): void
    {
        $this->postJson('/api/user/register', $this->payload(['email' => 'new@example.com']))->assertJsonPath('success', true);
        $otp = UserOtp::first();
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => $otp->otp])
            ->assertJsonPath('success', true)->assertJsonPath('data.email', 'new@example.com');
        $this->assertSame('new@example.com', User::first()->email);
        $this->postJson('/api/user/login', ['email' => 'new@example.com', 'password' => 'secret123', 'user_type' => 'buyer'])
            ->assertJsonPath('success', true);
    }

    public function test_wrong_expired_otp_and_account_type_requirements_are_preserved(): void
    {
        $this->postJson('/api/user/register', $this->payload(['user_type' => 'vendor']))->assertStatus(422);
        $this->postJson('/api/user/register', $this->payload(['user_type' => 'invalid']))->assertStatus(422);
        $this->postJson('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $otp = UserOtp::first();
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => 'wrong'])->assertJsonPath('success', false);
        $otp->update(['expire_at' => now()->subMinute()]);
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => $otp->otp])->assertStatus(403);
        $this->assertSame(0, User::count());
    }

    public function test_dashboard_create_and_update_use_optional_email_and_same_mobile_rules(): void
    {
        foreach ([new \App\Http\Requests\Dashboard\User\StoreUserRequest(), new \App\Http\Requests\Dashboard\User\UpdateUserRequest()] as $request) {
            $request->merge($this->payload(['email' => '  ']));
            $route = new \Illuminate\Routing\Route('PUT', 'users/{user?}', []);
            $route->bind(\Illuminate\Http\Request::create('/users', 'PUT'));
            $request->setRouteResolver(fn () => $route);
            $method = new \ReflectionMethod($request, 'prepareForValidation');
            $method->setAccessible(true); $method->invoke($request);
            $rules = $request->rules();
            $rules = array_intersect_key($rules, array_flip(['email', 'phone']));
            $this->assertNull($request->email);
            $this->assertSame('+966512345678', $request->phone);
            $this->assertTrue(Validator::make($request->all(), $rules)->passes());
            $this->assertTrue(Validator::make(['phone' => 'bad'], $rules)->fails());
            $this->assertTrue(Validator::make(['phone' => $request->phone, 'email' => 'bad'], $rules)->fails());
        }
    }

    /** @dataProvider supportedMobiles */
    public function test_supported_formats_register_and_verify_with_canonical_phone(string $phone, string $canonical): void
    {
        $this->postJson('/api/user/register', $this->payload(['phone' => $phone]))
            ->assertJsonPath('success', true)->assertJsonPath('data.phone', $canonical);
        $otp = UserOtp::first();
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $phone, 'otp' => $otp->otp])
            ->assertJsonPath('success', true)->assertJsonPath('data.phone', $canonical);
    }

    public static function supportedMobiles(): array
    {
        return [
            ['512345678', '+966512345678'], ['+966 (51) 234-5678', '+966512345678'],
            ['966512345678', '+966512345678'], ['00966512345678', '+966512345678'],
            ['01012345678', '+201012345678'], ['+20 (10) 1234-5678', '+201012345678'],
            ['00201012345678', '+201012345678'], ['201012345678', '+201012345678'],
            ['+44 7700 900123', '+447700900123'],
        ];
    }

    /** @dataProvider supportedMobiles */
    public function test_sms_and_whatsapp_use_the_same_destination(string $phone, string $canonical): void
    {
        config(['services.infobip.base_url' => 'https://otp.example.test', 'services.infobip.api_key' => 'test',
            'services.infobip.whatsapp_from' => 'test', 'services.infobip.whatsapp_template' => 'test',
            'services.sms.endpoint' => 'https://sms.example.test', 'services.sms.username' => 'test',
            'services.sms.password' => 'test']);
        \Illuminate\Support\Facades\Http::swap(new \Illuminate\Http\Client\Factory());
        \Illuminate\Support\Facades\Http::fake(function ($request) {
            if ($request->url() === 'https://otp.example.test/whatsapp/1/message/template') {
                return \Illuminate\Support\Facades\Http::response(['messages' => [['messageId' => 'test', 'status' => ['groupName' => 'PENDING']]]]);
            }
            if ($request->url() === 'https://sms.example.test') {
                return \Illuminate\Support\Facades\Http::response([]);
            }
            throw new \RuntimeException('Unexpected HTTP request in destination test');
        });
        // Resolve the real service, rather than the no-send registration mock.
        $service = new \App\Services\SmsService();
        $this->assertTrue($service->sendWhatsappOtp($phone, '0123')['success']);
        $this->assertTrue($service->send($phone, 'test')['success']);
        \Illuminate\Support\Facades\Http::assertSent(fn ($r) => ($r['messages'][0]['to'] ?? null) === substr($canonical, 1));
        \Illuminate\Support\Facades\Http::assertSent(fn ($r) => ($r['SMSReceiver'] ?? null) === substr($canonical, 1));
    }

    public function test_database_rejects_a_losing_prevalidated_registration_and_allows_null_emails(): void
    {
        $payload = $this->payload();
        $request = new \App\Http\Requests\api\User\Auth\RegisterRequest();
        $rules = $request->rules();
        $this->assertTrue(Validator::make($payload, $rules)->passes());
        $this->assertTrue(Validator::make($payload, $rules)->passes());
        User::create(['phone' => '+966512345678', 'email' => null]);
        try {
            User::create(['phone' => '0512345678', 'email' => null]);
            $this->fail('The database must reject a second canonical phone');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('phone', \App\Exceptions\Handler::identityConflict($e));
            $response = app(\App\Exceptions\Handler::class)->render(\Illuminate\Http\Request::create('/api/user/register'), $e);
            $this->assertSame(422, $response->getStatusCode());
            $this->assertFalse($response->getData()->success);
        }
        User::create(['phone' => '+966599999999', 'email' => null]);
        $this->assertSame(2, User::count());
    }

    public function test_email_uniqueness_matches_soft_delete_policy_and_case_normalization(): void
    {
        $user = User::create(['phone' => '+966599999999', 'email' => 'Used@Example.com']);
        $this->postJson('/api/user/register', $this->payload(['email' => ' used@example.com ']))->assertStatus(422);
        $user->delete();
        $this->postJson('/api/user/register', $this->payload(['email' => 'used@example.com']))->assertJsonPath('success', true);
        $this->postJson('/api/user/register', $this->payload(['phone' => '0599999999']))->assertStatus(422);
    }

    public function test_dashboard_phone_login_supports_null_email_and_preserves_admin_guard(): void
    {
        $admin = \App\Models\Admin::create(['phone' => '+966512345678', 'email' => null, 'password' => bcrypt('secret123')]);
        $this->post(route('admin.auth.login'), ['email' => '0512345678', 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
    }

    public function test_dashboard_email_login_still_works(): void
    {
        $admin = \App\Models\Admin::create(['email' => 'admin@example.com', 'phone' => '+966512345678', 'password' => bcrypt('secret123')]);
        $this->post(route('admin.auth.login'), ['email' => $admin->email, 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_dashboard_invalid_credentials_are_throttled_and_cannot_authenticate(): void
    {
        $admin = \App\Models\Admin::create(['phone' => '+966512345678', 'password' => bcrypt('secret123')]);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.auth.login'), ['phone' => '0512345678', 'password' => 'wrong'])->assertRedirect();
        }
        $this->post(route('admin.auth.login'), ['phone' => '+966512345678', 'password' => 'secret123'])->assertRedirect();
        $this->assertGuest('admin');
        $this->travel(61)->seconds();
        $this->post(route('admin.auth.login'), ['phone' => '0512345678', 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->travelBack();
    }

    public function test_profile_completion_keeps_email_optional_and_updates_canonical_phone(): void
    {
        $user = User::create(['phone' => '+966512345678', 'user_name' => 'before', 'email' => null]);
        \Laravel\Passport\Passport::actingAs($user);
        $this->postJson('/api/user/profile_completed', ['phone' => '0512345678', 'email' => '',
            'user_name' => 'updated', 'birth_date' => '1990-01-01'])->assertJsonPath('success', true);
        $this->assertNull($user->fresh()->email);
        $this->assertSame('+966512345678', $user->fresh()->phone);
    }

    /** @dataProvider dashboardUserTypes */
    public function test_dashboard_creates_and_edits_users_without_email(string $resource, string $type): void
    {
        $operator = \App\Models\Admin::create(['phone' => '+966599999999', 'type' => 'admin']);
        $this->actingAs($operator, 'admin');
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->post(route('admin.'.$resource.'.store'), $this->payload(['city_id' => 1]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $user = User::where('phone', '+966512345678')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertSame($type, $user->user_type);
        foreach (['provided@example.com', ''] as $email) {
            $this->put(route('admin.'.$resource.'.update', $user->id), ['name' => 'Updated User', 'phone' => '0512345678', 'email' => $email])
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame($email === '' ? null : $email, $user->fresh()->email);
        }
    }

    public static function dashboardUserTypes(): array
    {
        return [['users', 'buyer'], ['vendors', 'seller']];
    }

    public function test_dashboard_partner_create_and_edit_keep_both_records_email_free(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $operator = \App\Models\Admin::create(['phone' => '+966599999999', 'type' => 'admin']);
        $this->actingAs($operator, 'admin');
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->post(route('admin.partners.store'), $this->payload([
            'commercial_register' => \Illuminate\Http\UploadedFile::fake()->create('register.pdf', 1, 'application/pdf'),
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $user = User::where('phone', '+966512345678')->firstOrFail();
        $partner = \App\Models\Admin::where('user_id', $user->id)->firstOrFail();
        $this->assertNull($user->email);
        $this->assertNull($partner->email);
        $this->put(route('admin.partners.update', $partner->id), ['name' => 'Updated Partner',
            'user_name' => $user->user_name, 'phone' => '0512345678', 'email' => ''])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($user->fresh()->email);
        $this->assertNull($partner->fresh()->email);
        $this->assertSame('+966512345678', $partner->fresh()->phone);
    }

    public function test_null_email_does_not_bypass_dashboard_authentication_or_permissions(): void
    {
        $this->post(route('admin.users.store'), $this->payload())->assertRedirect();
        $this->assertSame(0, User::count());
        $operator = \App\Models\Admin::create(['phone' => '+966599999999', 'type' => 'admin']);
        $this->actingAs($operator, 'admin');
        \Illuminate\Support\Facades\Gate::before(fn () => false);
        $this->post(route('admin.users.store'), $this->payload(['city_id' => 1]))->assertForbidden();
        $this->assertSame(0, User::count());
    }

    public function test_delivery_rejects_malformed_numbers_without_contacting_providers(): void
    {
        \Illuminate\Support\Facades\Http::fake();
        $service = new \App\Services\SmsService();
        $this->assertFalse($service->sendWhatsappOtp('+96612345678', '1234')['success']);
        $this->assertFalse($service->send('+96612345678', 'test')['success']);
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    public function test_login_resolves_legacy_phone_without_rewriting_the_user(): void
    {
        $user = User::create(['phone' => '01012345678', 'password' => bcrypt('secret123'), 'user_type' => 'buyer_vendor', 'is_verified' => true]);
        foreach (['buyer_vendor'] as $type) {
            $this->postJson('/api/user/login', ['phone' => '+201012345678', 'password' => 'secret123', 'user_type' => $type])
                ->assertJsonPath('success', true);
        }
        $this->postJson('/api/user/login', ['phone' => '+201012345678', 'password' => 'secret123', 'user_type' => 'seller'])
            ->assertJsonPath('success', false);
        $this->assertSame('01012345678', $user->fresh()->phone);
    }

    public function test_production_registration_normalizes_phone_without_delivery(): void
    {
        app()->instance('env', 'production');
        config(['services.otp.qa_phones' => []]);
        $this->mock(\App\Services\SmsService::class, function ($mock) {
            $mock->shouldNotReceive('sendRegistrationOtp');
        });
        $this->postJson('/api/user/register', $this->payload(['phone' => '01012345678']))
            ->assertJsonPath('success', true)->assertJsonPath('data.phone', '+201012345678');
    }

    public function test_registration_otp_is_invalid_at_exact_expiry(): void
    {
        $this->travelTo(now()->startOfSecond());
        $this->postJson('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $otp = UserOtp::first();
        $otp->update(['expire_at' => now()]);
        $this->postJson('/api/user/verify-account', ['challenge_id' => $this->registrationChallengeId(), 'phone' => $otp->phone, 'otp' => $otp->otp])->assertStatus(403);
        $this->assertSame(0, User::count());
        $this->travelBack();
    }
}
