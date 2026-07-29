<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PartnerController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The partners list (/admin/partners) only showed name/email — missing
 * status, national ID and created-at (all present on the underlying
 * records already), and its row actions were a single generic-looking
 * dropdown missing a real "view details" page. This adds the missing
 * columns/page and wires "deactivate"/"delete"/"view details" to real,
 * working logic — matching the round icon-button component and the
 * dropdown structure (view details / edit / activate-deactivate / delete)
 * from the design reference.
 */
class PartnersActionsAndColumnsTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(array $permissions = ['view partners', 'edit partner', 'delete partner']): Admin
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

    private function createPartner(array $overrides = []): Admin
    {
        $user = User::create([
            'name' => 'Partner User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
            'national_id' => '1010257004',
            'is_active' => $overrides['is_active'] ?? true,
        ]);

        return Admin::create(array_merge([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'national_id' => '1010257004',
            'user_id' => $user->id,
        ], array_diff_key($overrides, ['is_active' => null])));
    }

    private function rowFor(Admin $partner): array
    {
        $request = Request::create('/admin/partners/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new PartnerController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $partner->id);
    }

    public function test_get_data_includes_national_id_status_and_created_at()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $partner = $this->createPartner(['is_active' => true]);

        $row = $this->rowFor($partner);

        $this->assertEquals('1010257004', $row['national_id']);
        $this->assertStringContainsString(TranslationHelper::translate('Active'), $row['status']);
        $this->assertEquals($partner->created_at->format('Y-m-d'), $row['created_at']);
    }

    public function test_get_data_status_reflects_the_linked_users_inactive_state()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $partner = $this->createPartner(['is_active' => false]);

        $row = $this->rowFor($partner);

        $this->assertStringContainsString(TranslationHelper::translate('Inactive'), $row['status']);
    }

    /**
     * Regression guard: view/edit were originally text items inside the
     * kebab dropdown, matching the design reference exactly. Per explicit
     * follow-up request, they were pulled out into their own standalone
     * icon buttons (matching the users/buyer table's pattern) — the kebab
     * dropdown keeps change-password/deactivate/delete only.
     */
    public function test_actions_have_standalone_view_and_edit_icons_plus_a_kebab_with_toggle_and_delete()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $partner = $this->createPartner();

        $row = $this->rowFor($partner);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.partners.show', $partner->id), $row['action']);
        $this->assertStringContainsString(route('admin.partners.edit', $partner->id), $row['action']);
        $this->assertStringContainsString(route('admin.partners.active_toogler', $partner->id), $row['action']);
        $this->assertStringContainsString(TranslationHelper::translate('deactivate'), $row['action']);
        $this->assertStringContainsString(TranslationHelper::translate('delete'), $row['action']);

        // View/edit must be standalone icons, not inside the dropdown-menu.
        $dropdownMenu = substr($row['action'], strpos($row['action'], 'dropdown-menu'), 600);
        $this->assertStringNotContainsString(route('admin.partners.show', $partner->id), $dropdownMenu);
        $this->assertStringNotContainsString(route('admin.partners.edit', $partner->id), $dropdownMenu);
    }

    public function test_show_page_renders_the_partners_details()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $partner = $this->createPartner();

        $html = (new PartnerController())->show($partner->id)->render();

        $this->assertStringContainsString($partner->name, $html);
        $this->assertStringContainsString('1010257004', $html);
    }

    public function test_show_page_aborts_without_view_partners_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions([]));

        $partner = $this->createPartner();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new PartnerController())->show($partner->id);
    }

    public function test_active_toogler_flips_the_linked_users_is_active_flag()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $partner = $this->createPartner(['is_active' => true]);
        $this->assertTrue((bool) $partner->user->is_active);

        (new PartnerController())->active_toogler($partner->id);

        $this->assertFalse((bool) $partner->user->fresh()->is_active);

        (new PartnerController())->active_toogler($partner->id);

        $this->assertTrue((bool) $partner->user->fresh()->is_active);
    }

    public function test_active_toogler_aborts_without_edit_partner_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view partners']));

        $partner = $this->createPartner();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new PartnerController())->active_toogler($partner->id);
    }

    public function test_destroy_deletes_the_partner()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $partner = $this->createPartner();
        $partnerId = $partner->id;

        (new PartnerController())->destroy($partnerId);

        $this->assertNull(Admin::find($partnerId));
    }

    public function test_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_partners_placeholder'), $html);
    }

    /**
     * The stat "brief" cards above the table (per explicit request, matching
     * a design reference): new partners this calendar month, inactive,
     * active, and total — all real counts, not hardcoded numbers. Status is
     * derived from the linked User's is_active flag (Admin itself has no
     * such column), same convention as the status badge column.
     */
    public function test_index_computes_real_stats_from_the_database()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $baseline = Admin::where('type', 'partner')->selectRaw('count(*) as total')->first();
        $baselineTotal = (int) $baseline->total;

        $activePartner = $this->createPartner(['is_active' => true]);
        $inactivePartnerA = $this->createPartner(['is_active' => false]);
        $inactivePartnerB = $this->createPartner(['is_active' => false]);

        $view = (new PartnerController())->index();
        $stats = $view->getData()['stats'];

        $this->assertEquals($baselineTotal + 3, $stats['total']);
        $this->assertGreaterThanOrEqual(1, $stats['active']);
        $this->assertGreaterThanOrEqual(2, $stats['inactive']);
        $this->assertEquals($stats['active'] + $stats['inactive'], $stats['total']);
    }

    public function test_index_new_this_month_only_counts_partners_created_in_the_current_calendar_month()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $baseline = Admin::where('type', 'partner')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $thisMonthPartner = $this->createPartner();
        $thisMonthPartner->created_at = now();
        $thisMonthPartner->save();

        $lastMonthPartner = $this->createPartner();
        $lastMonthPartner->created_at = now()->subMonthNoOverflow();
        $lastMonthPartner->save();

        $view = (new PartnerController())->index();
        $stats = $view->getData()['stats'];

        $this->assertEquals($baseline + 1, $stats['new_this_month']);
    }

    public function test_index_page_renders_the_stat_cards()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('new_this_month'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('inactive_partners'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('active_partners'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('total_partners'), $html);
    }
}
