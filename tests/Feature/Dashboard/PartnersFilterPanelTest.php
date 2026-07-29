<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PartnerController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the partners-page filter panel added on top of
 * PartnerController::get_data() (name/email/status/date range — the same
 * pattern already built for buyers/vendors/auctions, built from real,
 * already-stored columns, no new schema), the new "commercial_register"
 * table column (already stored on the linked vendor User, just not
 * previously displayed), and the deactivate/delete dropdown items being
 * rendered in red.
 */
class PartnersFilterPanelTest extends TestCase
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
            'name' => $overrides['name'] ?? 'Partner User',
            'email' => $overrides['email'] ?? ('vendor' . random_int(100000, 999999) . '@example.com'),
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
            'national_id' => '1010257004',
            'is_active' => $overrides['is_active'] ?? true,
            'commercial_register' => $overrides['commercial_register'] ?? null,
        ]);

        return Admin::create([
            'name' => $overrides['name'] ?? 'Partner Admin',
            'email' => $overrides['email'] ?? ('partner' . random_int(100000, 999999) . '@example.com'),
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'national_id' => '1010257004',
            'user_id' => $user->id,
            'created_at' => $overrides['created_at'] ?? now(),
        ]);
    }

    private function callGetData(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $request = Request::create('/admin/partners/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $params));
        app()->instance('request', $request);

        $response = (new PartnerController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data']);
    }

    public function test_filter_name_matches_by_partial_name()
    {
        $match = $this->createPartner(['name' => 'Special Partner Co']);
        $other = $this->createPartner(['name' => 'Someone Else']);

        $ids = $this->callGetData(['filter_name' => 'Special Partner'])->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_email_matches_by_partial_email()
    {
        $match = $this->createPartner(['email' => 'unique.match@example.com']);
        $other = $this->createPartner(['email' => 'nomatch@example.com']);

        $ids = $this->callGetData(['filter_email' => 'unique.match'])->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_status_active_excludes_inactive_partners()
    {
        $active = $this->createPartner(['is_active' => true]);
        $inactive = $this->createPartner(['is_active' => false]);

        $ids = $this->callGetData(['filter_status' => '1'])->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_filter_status_inactive_excludes_active_partners()
    {
        $active = $this->createPartner(['is_active' => true]);
        $inactive = $this->createPartner(['is_active' => false]);

        $ids = $this->callGetData(['filter_status' => '0'])->pluck('id');

        $this->assertFalse($ids->contains($active->id));
        $this->assertTrue($ids->contains($inactive->id));
    }

    public function test_filter_date_range_excludes_partners_outside_the_range()
    {
        $inRange = $this->createPartner(['created_at' => now()->subDays(5)]);
        $outOfRange = $this->createPartner(['created_at' => now()->subDays(30)]);

        $ids = $this->callGetData([
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ])->pluck('id');

        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_combined_filters_apply_together_as_an_intersection()
    {
        $match = $this->createPartner(['name' => 'Combo Match', 'is_active' => true]);
        $wrongName = $this->createPartner(['name' => 'Nope', 'is_active' => true]);
        $wrongStatus = $this->createPartner(['name' => 'Combo Match', 'is_active' => false]);

        $ids = $this->callGetData([
            'filter_name' => 'Combo Match',
            'filter_status' => '1',
        ])->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongName->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $partner = $this->createPartner();

        $ids = $this->callGetData([])->pluck('id');

        $this->assertTrue($ids->contains($partner->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_partners_index_page()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $view = (new PartnerController())->index();
        $html = $view->render();

        $this->assertStringContainsString('id="partnersFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_name"', $html);
        $this->assertStringContainsString('id="filter_email"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
        $this->assertStringContainsString('md-wide-search', $html);
    }

    public function test_commercial_register_column_shows_a_dash_when_not_uploaded()
    {
        $partner = $this->createPartner(['commercial_register' => null]);

        $row = $this->callGetData([])->firstWhere('id', $partner->id);

        $this->assertEquals('-', $row['commercial_register']);
    }

    public function test_commercial_register_column_shows_a_link_when_uploaded()
    {
        $partner = $this->createPartner(['commercial_register' => 'vendor-commercial-files/sample.pdf']);

        $row = $this->callGetData([])->firstWhere('id', $partner->id);

        $this->assertStringContainsString('<a href=', $row['commercial_register']);
        $this->assertStringContainsString(Storage::disk('public')->url('vendor-commercial-files/sample.pdf'), $row['commercial_register']);
    }

    /**
     * Explicit request: the "deactivate" and "delete" dropdown items must be
     * red text. "Activate" (the opposite state of the same toggle link) is
     * not a destructive action, so it stays the default color.
     */
    public function test_deactivate_and_delete_dropdown_items_are_red_text()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $activePartner = $this->createPartner(['is_active' => true]);
        $html = view('dashboard.pages.partners.actions', ['item' => $activePartner])->render();

        $deactivatePos = strpos($html, TranslationHelper::translate('deactivate') ?? '');
        // Locate the specific <a> wrapping the deactivate text and assert it carries text-danger.
        $anchorStart = strrpos(substr($html, 0, $deactivatePos), '<a ');
        $anchorTag = substr($html, $anchorStart, $deactivatePos - $anchorStart);
        $this->assertStringContainsString('text-danger', $anchorTag);

        $this->assertMatchesRegularExpression(
            '/class="dropdown-item text-danger" href="#deleteModal-' . $activePartner->id . '"/',
            $html
        );
    }

    public function test_activate_dropdown_item_is_not_red_text()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $inactivePartner = $this->createPartner(['is_active' => false]);
        $html = view('dashboard.pages.partners.actions', ['item' => $inactivePartner])->render();

        $activatePos = strpos($html, TranslationHelper::translate('activate') ?? '');
        $anchorStart = strrpos(substr($html, 0, $activatePos), '<a ');
        $anchorTag = substr($html, $anchorStart, $activatePos - $anchorStart);
        $this->assertStringNotContainsString('text-danger', $anchorTag);
    }
}
