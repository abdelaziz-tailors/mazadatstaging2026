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
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'services.otp.testing' => true, 'app.dashboard_locale' => 'en', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');
        app('laravellocalization')->setSupportedLocales(['en' => ['name' => 'English', 'native' => 'English']]);
        app()->setLocale('en');
        foreach (['users', 'user_otps', 'admins'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                foreach (['name', 'user_name', 'email', 'phone', 'national_id', 'user_type', 'password',
                    'commercial_register', 'otp', 'password_otp', 'type', 'city_id', 'admin_id', 'user_id'] as $field) {
                    $table->string($field)->nullable();
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
        $this->postJson('/api/user/verify-account', ['phone' => '0512345678', 'otp' => $otp->otp])
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
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])->assertJsonPath('success', false);
        $this->assertSame(1, User::count());
    }

    /** @dataProvider accountTypes */
    public function test_email_free_verification_preserves_account_types(string $type): void
    {
        $this->postJson('/api/user/register', $this->payload())->assertJsonPath('success', true);
        $otp = UserOtp::first();
        // Vendor file validation is exercised separately; verify its linked admin here.
        $otp->update(['user_type' => $type]);
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])
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
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])
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
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => 'wrong'])->assertJsonPath('success', false);
        $otp->update(['expire_at' => now()->subMinute()]);
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])->assertStatus(403);
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
        $this->postJson('/api/user/verify-account', ['phone' => $phone, 'otp' => $otp->otp])
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
        \Illuminate\Support\Facades\Http::fake([
            'otp.example.test/*' => \Illuminate\Support\Facades\Http::response(['messages' => [['messageId' => 'test', 'status' => ['groupName' => 'PENDING']]]]),
            'sms.example.test' => \Illuminate\Support\Facades\Http::response([]),
        ]);
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
        $user = User::create(['phone' => '01012345678', 'password' => bcrypt('secret123'), 'user_type' => 'buyer_vendor']);
        foreach (['buyer', 'vendor'] as $type) {
            $this->postJson('/api/user/login', ['phone' => '+201012345678', 'password' => 'secret123', 'user_type' => $type])
                ->assertJsonPath('success', true);
        }
        $this->postJson('/api/user/login', ['phone' => '+201012345678', 'password' => 'secret123', 'user_type' => 'seller'])
            ->assertStatus(422)->assertJsonPath('success', false);
        $this->assertSame('01012345678', $user->fresh()->phone);
    }

    public function test_registration_sends_the_canonical_phone_when_testing_mode_is_disabled(): void
    {
        config(['services.otp.testing' => false]);
        $this->mock(\App\Services\SmsService::class, function ($mock) {
            $mock->shouldReceive('sendRegistrationOtp')->once()->with('+201012345678', \Mockery::type('string'))
                ->andReturn(['success' => true]);
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
        $this->postJson('/api/user/verify-account', ['phone' => $otp->phone, 'otp' => $otp->otp])->assertStatus(403);
        $this->assertSame(0, User::count());
        $this->travelBack();
    }
}
