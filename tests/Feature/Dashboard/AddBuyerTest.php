<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Requests\Dashboard\User\StoreUserRequest;
use App\Models\Admin;
use App\Models\City;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the new "add buyer" flow on /admin/users?user_type=buyer — an
 * admin-only entry point (per explicit request, matching a reference design:
 * full name, alias/username, phone, email, password, city — but with no
 * terms-and-conditions checkbox, since the admin is the one adding the
 * account, not the buyer consenting themselves).
 *
 * The gender field was dropped per a later explicit request — the mobile
 * app's own registration flow never collects it either, so requiring it
 * only here was an inconsistency. "gender" is a NOT NULL enum column with
 * no default, but omitting it from the INSERT entirely (rather than passing
 * an empty value) works fine against this server's non-strict SQL mode —
 * confirmed empirically, and it's exactly what the mobile registration path
 * already does.
 *
 * Controller-level tests here call UserController::store() directly with a
 * manually-built StoreUserRequest (same pattern as every other controller
 * test in this suite) — that path never runs through Laravel's HTTP kernel,
 * so StoreUserRequest::prepareForValidation() (which normalizes the raw
 * 9-digit phone into the "+9665XXXXXXXX" format already used by every real
 * stored phone number) never fires either. Payloads built here already use
 * the normalized format directly to match what the controller actually
 * receives on a real request. prepareForValidation() itself is verified
 * separately, in isolation, via reflection.
 */
class AddBuyerTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermission(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach (['add user', 'view users'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function createNoPermissionAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'No Permission Admin',
            'email' => 'noperm' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        $permission = Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    private function createCity(): City
    {
        return City::create([
            'name' => json_encode(['ar' => 'الرياض', 'en' => 'Riyadh']),
            'is_active' => 1,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        $cityId = $overrides['city_id'] ?? $this->createCity()->id;

        return array_merge([
            'name' => 'Ahmed Ali',
            'user_name' => 'ahmed' . random_int(100000, 999999),
            'phone' => '+966' . '5' . random_int(10000000, 99999999),
            'email' => 'ahmed' . random_int(100000, 999999) . '@example.com',
            'password' => 'secret1234',
            'city_id' => $cityId,
        ], array_merge($overrides, ['city_id' => $cityId]));
    }

    private function submitAsPermittedAdmin(array $payload): void
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermission());
        $request = new StoreUserRequest();
        $request->merge($payload);
        (new UserController())->store($request);
    }

    public function test_create_page_renders_the_expected_fields_and_no_terms_checkbox()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermission());
        view()->share('errors', new ViewErrorBag());
        $this->createCity();

        $html = (new UserController())->create()->render();

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="user_name"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="city_id"', $html);
        $this->assertStringContainsString('+966', $html);
        // No terms-and-conditions checkbox — the admin adds this account, not the buyer.
        $this->assertStringNotContainsString('terms', strtolower($html));
        // Gender was dropped — the mobile app registration flow never
        // collects it either, so requiring it only here was inconsistent.
        $this->assertStringNotContainsString('name="gender"', $html);
    }

    public function test_create_page_aborts_without_add_user_permission()
    {
        Auth::guard('admin')->setUser($this->createNoPermissionAdmin());

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new UserController())->create();
    }

    public function test_store_creates_a_real_buyer_with_the_submitted_data()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermission());

        $city = $this->createCity();
        $payload = $this->validPayload(['city_id' => $city->id, 'phone' => '+966512345678']);

        $request = new StoreUserRequest();
        $request->merge($payload);
        (new UserController())->store($request);

        $user = User::where('user_name', $payload['user_name'])->first();

        $this->assertNotNull($user);
        $this->assertEquals('buyer', $user->user_type);
        $this->assertEquals(1, $user->is_active);
        $this->assertEquals('+966512345678', $user->phone);
        $this->assertEquals($city->id, $user->city_id);
        $this->assertTrue(Hash::check('secret1234', $user->password));
    }

    public function test_store_aborts_without_add_user_permission()
    {
        Auth::guard('admin')->setUser($this->createNoPermissionAdmin());

        $request = new StoreUserRequest();
        $request->merge($this->validPayload());

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new UserController())->store($request);
    }

    /**
     * The form only collects the 9-digit local number next to a fixed
     * "+966" badge — prepareForValidation() is what turns that into the
     * full stored format. Verified directly since the controller-level
     * tests above bypass Laravel's request-validation pipeline entirely.
     */
    public function test_prepare_for_validation_normalizes_the_raw_local_phone_number()
    {
        $request = new StoreUserRequest();
        $request->merge(['phone' => '512345678']);

        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('+966512345678', $request->phone);
    }

    public function test_prepare_for_validation_strips_a_leading_zero_before_normalizing()
    {
        $request = new StoreUserRequest();
        $request->merge(['phone' => '0512345678']);

        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('+966512345678', $request->phone);
    }

    public function test_validation_rejects_missing_required_fields()
    {
        $validator = Validator::make([], (new StoreUserRequest())->rules());

        $this->assertTrue($validator->fails());
        foreach (['name', 'user_name', 'phone', 'email', 'password', 'city_id'] as $field) {
            $this->assertArrayHasKey($field, $validator->errors()->toArray());
        }
    }

    public function test_validation_rejects_a_phone_number_not_matching_the_saudi_mobile_format()
    {
        $payload = array_merge($this->validPayload(), ['phone' => '12345']);
        $validator = Validator::make($payload, (new StoreUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_phone_number()
    {
        $existing = $this->validPayload();
        $this->submitAsPermittedAdmin($existing);

        $payload = array_merge($this->validPayload(), ['phone' => $existing['phone']]);
        $validator = Validator::make($payload, (new StoreUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_username()
    {
        $existing = $this->validPayload();
        $this->submitAsPermittedAdmin($existing);

        $payload = array_merge($this->validPayload(), ['user_name' => $existing['user_name']]);
        $validator = Validator::make($payload, (new StoreUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_name', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_email()
    {
        $existing = $this->validPayload();
        $this->submitAsPermittedAdmin($existing);

        $payload = array_merge($this->validPayload(), ['email' => $existing['email']]);
        $validator = Validator::make($payload, (new StoreUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * "gender" is a NOT NULL enum('male','female') column with no explicit
     * default — omitting it from the INSERT doesn't error under this
     * server's non-strict SQL mode, but MySQL silently falls back to the
     * enum's first defined value ('male') rather than storing a true NULL.
     * That's an existing DB-level quirk (the mobile registration flow,
     * which also never collects gender, has the exact same behavior) — not
     * something introduced or fixed here. This just documents the real,
     * observed outcome of no longer submitting the field.
     */
    public function test_store_creates_a_real_buyer_without_collecting_gender()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermission());

        $payload = $this->validPayload();
        $request = new StoreUserRequest();
        $request->merge($payload);
        (new UserController())->store($request);

        $user = User::where('user_name', $payload['user_name'])->first();

        $this->assertNotNull($user);
        $this->assertArrayNotHasKey('gender', $payload);
        // MySQL's implicit enum default under non-strict mode — confirmed
        // directly against this DB, not assumed.
        $this->assertEquals('male', $user->gender);
    }

    public function test_validation_rejects_a_city_id_that_does_not_exist()
    {
        $payload = array_merge($this->validPayload(), ['city_id' => 999999]);
        $validator = Validator::make($payload, (new StoreUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('city_id', $validator->errors()->toArray());
    }

    public function test_validation_passes_for_a_fully_valid_payload()
    {
        $validator = Validator::make($this->validPayload(), (new StoreUserRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * The "add new buyer" button must only appear on the buyers-filtered
     * view, and only for admins with the "add user" permission.
     */
    public function test_add_buyer_button_only_shows_on_buyers_view_with_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermission());
        view()->share('errors', new ViewErrorBag());

        $buyersHtml = (new UserController())->index(new Request(['user_type' => 'buyer']))->render();
        $this->assertStringContainsString(route('admin.users.create'), $buyersHtml);
        $this->assertStringContainsString(TranslationHelper::translate('add_new_buyer'), $buyersHtml);

        $allUsersHtml = (new UserController())->index(new Request())->render();
        $this->assertStringNotContainsString(route('admin.users.create'), $allUsersHtml);
    }

    public function test_add_buyer_button_is_hidden_without_permission()
    {
        Auth::guard('admin')->setUser($this->createNoPermissionAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserController())->index(new Request(['user_type' => 'buyer']))->render();

        $this->assertStringNotContainsString(route('admin.users.create'), $html);
    }
}
