<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\VendorController;
use App\Http\Requests\Dashboard\Vendor\StoreVendorRequest;
use App\Models\Admin;
use App\Models\City;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Covers the redesigned "add vendor" form on /admin/vendors/create — per
 * explicit request, rebuilt to match the buyer creation flow exactly (see
 * AddBuyerTest): full name, alias/username, phone, email, password, city,
 * with matching validation. The old form only collected name/email/phone
 * and never set a password at all, so vendors created through it could
 * never log in — this fixes that too.
 *
 * The gender field was dropped per a later explicit request — same
 * reasoning as AddBuyerTest: the mobile app's own registration flow never
 * collects it, so requiring it only in the dashboard form was inconsistent.
 * "gender" is a NOT NULL enum column with no default; omitting it from the
 * INSERT doesn't error (non-strict SQL mode), but MySQL silently stores its
 * implicit first enum value ('male') rather than a true NULL — confirmed
 * directly against this DB, not assumed.
 *
 * Vendors stay scoped to the creating admin via admin_id (unchanged from
 * before — matches VendorController::index()/get_data()'s existing
 * admin_id scoping).
 *
 * Same controller-level testing pattern as AddBuyerTest: store() is called
 * directly with a manually-built StoreVendorRequest, which never runs
 * through Laravel's HTTP kernel — so prepareForValidation() (phone
 * normalization) doesn't fire on that path either. Payloads here already
 * use the normalized "+966XXXXXXXXX" format to match what the controller
 * actually receives on a real request; prepareForValidation() itself is
 * verified separately, in isolation, via reflection.
 */
class AddVendorTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    private function createCity(): City
    {
        return City::create([
            'name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']),
            'is_active' => 1,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        $cityId = $overrides['city_id'] ?? $this->createCity()->id;

        return array_merge([
            'name' => 'Vendor Name',
            'user_name' => 'vendor' . random_int(100000, 999999),
            'phone' => '+966' . '5' . random_int(10000000, 99999999),
            'email' => 'vendor' . random_int(100000, 999999) . '@example.com',
            'password' => 'secret1234',
            'city_id' => $cityId,
        ], array_merge($overrides, ['city_id' => $cityId]));
    }

    private function submitAsAdmin(Admin $admin, array $payload): void
    {
        Auth::guard('admin')->setUser($admin);
        $request = new StoreVendorRequest();
        $request->merge($payload);
        (new VendorController())->store($request);
    }

    public function test_create_page_renders_the_same_fields_as_the_buyer_form()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        $this->createCity();

        $html = (new VendorController())->create()->render();

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="user_name"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="city_id"', $html);
        $this->assertStringContainsString('+966', $html);
        $this->assertStringNotContainsString('terms', strtolower($html));
        // Gender was dropped — the mobile app registration flow never
        // collects it either, so requiring it only here was inconsistent.
        $this->assertStringNotContainsString('name="gender"', $html);
    }

    public function test_store_creates_a_real_vendor_scoped_to_the_creating_admin_with_a_usable_password()
    {
        $admin = $this->createAdmin();
        $city = $this->createCity();
        $payload = $this->validPayload(['city_id' => $city->id, 'phone' => '+966512345678']);

        $this->submitAsAdmin($admin, $payload);

        $vendor = User::where('user_name', $payload['user_name'])->first();

        $this->assertNotNull($vendor);
        $this->assertEquals('vendor', $vendor->user_type);
        $this->assertEquals($admin->id, $vendor->admin_id);
        $this->assertEquals(1, $vendor->is_active);
        $this->assertEquals('+966512345678', $vendor->phone);
        $this->assertEquals($city->id, $vendor->city_id);
        $this->assertTrue(Hash::check('secret1234', $vendor->password));
    }

    public function test_store_creates_a_real_vendor_without_collecting_gender()
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();

        $this->submitAsAdmin($admin, $payload);

        $vendor = User::where('user_name', $payload['user_name'])->first();

        $this->assertNotNull($vendor);
        $this->assertArrayNotHasKey('gender', $payload);
        // MySQL's implicit enum default under non-strict mode — confirmed
        // directly against this DB, not assumed.
        $this->assertEquals('male', $vendor->gender);
    }

    public function test_two_different_admins_vendors_are_scoped_independently()
    {
        $adminA = $this->createAdmin();
        $adminB = $this->createAdmin();

        $payloadA = $this->validPayload();
        $this->submitAsAdmin($adminA, $payloadA);
        $vendorA = User::where('user_name', $payloadA['user_name'])->first();

        $payloadB = $this->validPayload();
        $this->submitAsAdmin($adminB, $payloadB);
        $vendorB = User::where('user_name', $payloadB['user_name'])->first();

        $this->assertEquals($adminA->id, $vendorA->admin_id);
        $this->assertEquals($adminB->id, $vendorB->admin_id);
        $this->assertNotEquals($vendorA->admin_id, $vendorB->admin_id);
    }

    public function test_prepare_for_validation_normalizes_the_raw_local_phone_number()
    {
        $request = new StoreVendorRequest();
        $request->merge(['phone' => '512345678']);

        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('+966512345678', $request->phone);
    }

    public function test_validation_rejects_missing_required_fields()
    {
        $validator = Validator::make([], (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        foreach (['name', 'user_name', 'phone', 'email', 'password', 'city_id'] as $field) {
            $this->assertArrayHasKey($field, $validator->errors()->toArray());
        }
    }

    public function test_validation_rejects_a_phone_number_not_matching_the_saudi_mobile_format()
    {
        $payload = array_merge($this->validPayload(), ['phone' => '12345']);
        $validator = Validator::make($payload, (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_phone_number()
    {
        $existing = $this->validPayload();
        $this->submitAsAdmin($this->createAdmin(), $existing);

        $payload = array_merge($this->validPayload(), ['phone' => $existing['phone']]);
        $validator = Validator::make($payload, (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_username()
    {
        $existing = $this->validPayload();
        $this->submitAsAdmin($this->createAdmin(), $existing);

        $payload = array_merge($this->validPayload(), ['user_name' => $existing['user_name']]);
        $validator = Validator::make($payload, (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_name', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_email()
    {
        $existing = $this->validPayload();
        $this->submitAsAdmin($this->createAdmin(), $existing);

        $payload = array_merge($this->validPayload(), ['email' => $existing['email']]);
        $validator = Validator::make($payload, (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_password_shorter_than_eight_characters()
    {
        $payload = array_merge($this->validPayload(), ['password' => 'short']);
        $validator = Validator::make($payload, (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_city_id_that_does_not_exist()
    {
        $payload = array_merge($this->validPayload(), ['city_id' => 999999]);
        $validator = Validator::make($payload, (new StoreVendorRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('city_id', $validator->errors()->toArray());
    }

    public function test_validation_passes_for_a_fully_valid_payload()
    {
        $validator = Validator::make($this->validPayload(), (new StoreVendorRequest())->rules());

        $this->assertFalse($validator->fails());
    }
}
