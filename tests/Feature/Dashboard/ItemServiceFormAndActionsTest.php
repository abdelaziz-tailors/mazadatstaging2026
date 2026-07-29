<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\ItemServiceController;
use App\Http\Requests\Dashboard\ItemService\StoreItemServiceRequest;
use App\Http\Requests\Dashboard\ItemService\UpdateItemServiceRequest;
use App\Models\Admin;
use App\Models\ItemService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Covers two explicit requests for /admin/item-services:
 *
 * 1. The actions column dropdown was replaced with plain edit/delete icon
 *    buttons matching the /admin/users?user_type=buyer list (md-icon-btn,
 *    delete in red via md-icon-btn-danger) — no more Bootstrap dropdown.
 *
 * 2. The create/edit form was restyled to match /admin/users/create's
 *    centered-card layout (page icon, breadcrumb, hr), and the English name
 *    field was removed entirely — only an Arabic name is collected/stored.
 *    Item services use their own StoreItemServiceRequest/
 *    UpdateItemServiceRequest (name.ar only) instead of the shared
 *    StoreNameRequest/UpdateNameRequest, which still require every locale
 *    and are used by categories/ages/animal-pens — untouched by this change.
 */
class ItemServiceFormAndActionsTest extends TestCase
{
    use DatabaseTransactions;

    private function createPartnerAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
    }

    private function createItemService(Admin $owner, array $overrides = []): ItemService
    {
        return ItemService::create(array_merge([
            'name' => json_encode(['ar' => 'خدمة الوزن']),
            'default_price' => 25,
            'sort_order' => 0,
            'admin_id' => $owner->id,
            'is_active' => true,
        ], $overrides));
    }

    public function test_create_page_only_has_the_arabic_name_field_and_matches_the_users_create_layout()
    {
        Auth::guard('admin')->setUser($this->createPartnerAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new ItemServiceController())->create()->render();

        $this->assertStringContainsString('name="name[ar]"', $html);
        $this->assertStringNotContainsString('name="name[en]"', $html);
        $this->assertStringContainsString('md-page-icon', $html);
        $this->assertStringContainsString('page-title', $html);
        $this->assertStringContainsString('breadcrumb', $html);
    }

    public function test_edit_page_prefills_the_arabic_name_and_has_no_english_field()
    {
        $admin = $this->createPartnerAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $itemService = $this->createItemService($admin, ['name' => json_encode(['ar' => 'خدمة النقل', 'en' => 'Transport'])]);

        $html = (new ItemServiceController())->edit($itemService->id)->render();

        $this->assertStringContainsString('خدمة النقل', $html);
        $this->assertStringNotContainsString('name="name[en]"', $html);
    }

    public function test_actions_view_renders_icon_buttons_instead_of_a_dropdown()
    {
        $admin = $this->createPartnerAdmin();
        $itemService = $this->createItemService($admin);

        $html = view('dashboard.pages.item-services.actions', ['item' => $itemService])->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString('md-icon-btn-danger', $html);
        $this->assertStringNotContainsString('dropdown-toggle', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
        $this->assertStringContainsString(route('admin.item-services.edit', $itemService->id), $html);
    }

    public function test_store_validation_only_requires_the_arabic_name()
    {
        $validator = Validator::make(['name' => ['ar' => 'خدمة جديدة']], (new StoreItemServiceRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_store_validation_rejects_a_missing_arabic_name()
    {
        $validator = Validator::make(['name' => []], (new StoreItemServiceRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name.ar', $validator->errors()->toArray());
    }

    public function test_store_creates_a_record_with_only_the_arabic_name_stored()
    {
        $admin = $this->createPartnerAdmin();
        Auth::guard('admin')->setUser($admin);

        $request = new StoreItemServiceRequest();
        $request->merge([
            'name' => ['ar' => 'خدمة تحميل'],
            'default_price' => 40,
            'sort_order' => 2,
            'is_active' => '1',
        ]);

        (new ItemServiceController())->store($request);

        $itemService = ItemService::where('admin_id', $admin->id)->latest('id')->first();
        $decoded = json_decode($itemService->name, true);

        $this->assertEquals('خدمة تحميل', $decoded['ar']);
        $this->assertArrayNotHasKey('en', $decoded);
        $this->assertEquals(40, $itemService->default_price);
    }

    public function test_update_replaces_the_stored_name_with_only_the_arabic_value()
    {
        $admin = $this->createPartnerAdmin();
        Auth::guard('admin')->setUser($admin);
        $itemService = $this->createItemService($admin, ['name' => json_encode(['ar' => 'قديم', 'en' => 'Old'])]);

        $request = new UpdateItemServiceRequest();
        $request->merge([
            'name' => ['ar' => 'جديد'],
            'default_price' => 55,
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        (new ItemServiceController())->update($request, $itemService->id);

        $decoded = json_decode($itemService->fresh()->name, true);
        $this->assertEquals('جديد', $decoded['ar']);
        $this->assertArrayNotHasKey('en', $decoded);
    }
}
