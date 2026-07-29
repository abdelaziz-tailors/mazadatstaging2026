<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: the sidebar's generic "المستخدمين" (all users, no
 * user_type filter) link was replaced with "البائعين" (vendors/sellers),
 * placed directly under "المشترين" (buyers) — per explicit request, since
 * this app's real user categories are مشتركين (auction-organizing tenants)
 * / بائعين (sellers, who submit items for an organizer to add to an
 * auction) / مشترين (buyers), not a separate catch-all "users" bucket.
 * The underlying unfiltered users.index route/page still exists (just
 * unlinked from navigation) — nothing was deleted.
 */
class SidebarBuyersVendorsOrderTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach (['view users', 'view vendors'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_sidebar_no_longer_links_the_unfiltered_users_index()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        // The buyer-filtered link ("...admin/users?user_type=buyer") must
        // stay — only the unfiltered "...admin/users" (no query string) link
        // should be gone.
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*\/admin\/users"/', $html);
    }

    public function test_sidebar_links_buyers_and_vendors_with_vendors_directly_after_buyers()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        // Path fragments rather than full route() URLs — avoids brittleness
        // from locale-prefix differences between this test's context and
        // the one the view actually rendered under.
        $buyersPos = strpos($html, 'admin/users?user_type=buyer');
        $vendorsPos = strpos($html, 'admin/vendors');

        $this->assertNotFalse($buyersPos, 'buyers link should be in the sidebar');
        $this->assertNotFalse($vendorsPos, 'vendors link should be in the sidebar');
        $this->assertLessThan($vendorsPos, $buyersPos, 'vendors link should come directly after buyers in the sidebar');

        // Nothing in between the two <li> entries besides the closing/opening tags.
        $between = substr($html, $buyersPos, $vendorsPos - $buyersPos);
        $this->assertLessThan(400, strlen($between), 'vendors should be the very next sidebar item after buyers');
    }

    /**
     * Regression guard: the vendors link used to be gated behind
     * "can('view vendors')" — a permission that was never actually
     * registered anywhere in the app (the whole 'vendors' permission group
     * is commented out in Permissions.php), so the gate silently returned
     * false for every admin, including Super Admin, hiding the link
     * entirely. VendorController itself enforces no permission check at
     * all, so the link is now unconditional to match.
     */
    public function test_vendors_link_shows_even_without_the_dead_view_vendors_permission()
    {
        $admin = Admin::create([
            'name' => 'No Permission Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('admin/vendors', $html);
    }
}
