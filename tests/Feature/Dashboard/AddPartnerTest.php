<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\PartnerController;
use App\Http\Requests\Dashboard\Partner\StorePartnerRequest;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the redesigned "add partner" form on /admin/partners/create — per
 * explicit request, restyled visually to match the vendor creation flow
 * (card layout, page icon, spacing, inline validation, password show/hide
 * toggle). Only the design changed: the partner form still collects its own
 * real fields (name, alias/username, email, phone, password,
 * commercial_register) and still creates both a User (user_type=vendor) and
 * a linked Admin (type=partner) record, exactly as PartnerController::store()
 * already did — no gender/city fields were added, since those don't exist
 * for partners and adding them wasn't requested.
 *
 * The phone field was later also matched to the vendor form's system, not
 * just its look: a fixed "+966" badge next to a 9-digit local-number input,
 * normalized by StorePartnerRequest::prepareForValidation() to
 * "+966XXXXXXXXX" before validating — see the vendor-format tests below.
 *
 * Same controller-level testing pattern as AddVendorTest: store() is called
 * directly with a manually-built StorePartnerRequest, which never runs
 * through Laravel's HTTP kernel — so prepareForValidation() (phone
 * normalization) doesn't fire on that path either. Payloads here already
 * use the normalized "+966XXXXXXXXX" format to match what the controller
 * actually receives on a real request; prepareForValidation() itself is
 * verified separately, in isolation, via reflection.
 */
class AddPartnerTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $permissions = ['add partner']): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Partner Name',
            'user_name' => 'partner' . random_int(100000, 999999),
            'phone' => '+9665' . random_int(10000000, 99999999),
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => 'secret1234',
            'commercial_register' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
        ], $overrides);
    }

    private function submitAsAdmin(Admin $admin, array $payload): void
    {
        Auth::guard('admin')->setUser($admin);
        $request = new StorePartnerRequest();
        $request->merge($payload);
        if (isset($payload['commercial_register'])) {
            $request->files->set('commercial_register', $payload['commercial_register']);
        }
        (new PartnerController())->store($request);
    }

    public function test_create_page_renders_the_same_style_as_the_vendor_form()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerController())->create()->render();

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="user_name"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="commercial_register"', $html);
        $this->assertStringContainsString('md-page-icon', $html);
        $this->assertStringContainsString('md-password-field', $html);
        $this->assertStringNotContainsString('name="gender"', $html);
        $this->assertStringNotContainsString('name="city_id"', $html);
    }

    public function test_store_creates_a_linked_user_and_admin_with_a_usable_password()
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $payload = $this->validPayload();

        $this->submitAsAdmin($admin, $payload);

        $vendorUser = User::where('user_name', $payload['user_name'])->first();
        $partnerAdmin = Admin::where('email', $payload['email'])->where('type', 'partner')->first();

        $this->assertNotNull($vendorUser);
        $this->assertEquals('vendor', $vendorUser->user_type);
        $this->assertTrue(Hash::check('secret1234', $vendorUser->password));

        $this->assertNotNull($partnerAdmin);
        $this->assertEquals($vendorUser->id, $partnerAdmin->user_id);
        $this->assertNotNull($vendorUser->commercial_register);
        Storage::disk('public')->assertExists($vendorUser->commercial_register);
    }

    public function test_validation_rejects_missing_required_fields()
    {
        $validator = Validator::make([], (new StorePartnerRequest())->rules());

        $this->assertTrue($validator->fails());
        foreach (['name', 'email', 'user_name', 'phone', 'commercial_register', 'password'] as $field) {
            $this->assertArrayHasKey($field, $validator->errors()->toArray());
        }
    }

    public function test_validation_rejects_a_duplicate_email()
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $existing = $this->validPayload();
        $this->submitAsAdmin($admin, $existing);

        $payload = array_merge($this->validPayload(), ['email' => $existing['email']]);
        $validator = Validator::make($payload, (new StorePartnerRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_duplicate_username()
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $existing = $this->validPayload();
        $this->submitAsAdmin($admin, $existing);

        $payload = array_merge($this->validPayload(), ['user_name' => $existing['user_name']]);
        $validator = Validator::make($payload, (new StorePartnerRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('user_name', $validator->errors()->toArray());
    }

    public function test_validation_rejects_a_password_shorter_than_six_characters()
    {
        $payload = array_merge($this->validPayload(), ['password' => 'abc']);
        $validator = Validator::make($payload, (new StorePartnerRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_prepare_for_validation_normalizes_the_raw_local_phone_number()
    {
        $request = new StorePartnerRequest();
        $request->merge(['phone' => '512345678']);

        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('+966512345678', $request->phone);
    }

    public function test_validation_rejects_a_phone_number_not_matching_the_saudi_mobile_format()
    {
        $payload = array_merge($this->validPayload(), ['phone' => '+9661234']);
        $validator = Validator::make($payload, (new StorePartnerRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_create_page_renders_the_phone_badge_and_local_number_input()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerController())->create()->render();

        $this->assertStringContainsString('md-phone-field', $html);
        $this->assertStringContainsString('+966', $html);
        $this->assertStringContainsString('maxlength="9"', $html);
    }

    public function test_validation_rejects_a_non_pdf_or_image_commercial_register()
    {
        $payload = array_merge($this->validPayload(), [
            'commercial_register' => UploadedFile::fake()->create('cr.txt', 10, 'text/plain'),
        ]);
        $validator = Validator::make($payload, (new StorePartnerRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('commercial_register', $validator->errors()->toArray());
    }

    public function test_validation_passes_for_a_fully_valid_payload()
    {
        $validator = Validator::make($this->validPayload(), (new StorePartnerRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_store_is_rejected_without_the_add_partner_permission()
    {
        Storage::fake('public');
        $admin = $this->createAdmin([]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->submitAsAdmin($admin, $this->validPayload());
    }
}
