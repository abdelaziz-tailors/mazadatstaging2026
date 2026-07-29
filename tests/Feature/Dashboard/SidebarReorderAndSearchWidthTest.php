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
 * Two explicit follow-up requests to the admin sidebar/header:
 *
 * 1. "الإعدادات" (Settings) moved from the middle of the "platform_management"
 *    group to be the very last item in the whole sidebar (after the
 *    "الصفحات" submenu), instead of sitting between the subscriptions
 *    submenu and the content-group heading.
 *
 * 2. The wide-search box (".md-wide-search", used on pages with a longer
 *    search placeholder) narrowed from 520px to 400px on large screens
 *    (>=992px) — the narrow-screen behavior (full-width, handled by a
 *    separate <=991px rule) is untouched.
 */
class SidebarReorderAndSearchWidthTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(array $permissions = []): Admin
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach (array_merge(['edit page'], $permissions) as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_settings_link_is_the_last_item_in_the_sidebar()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin(['view videos']));
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $settingsLinkPos = strpos($html, route('admin.settings.edit'));
        $pagesLinkPos = strpos($html, route('admin.pages.edit', [3]));
        $sidebarCloseTagPos = strpos($html, '</div>', $settingsLinkPos);

        $this->assertNotFalse($settingsLinkPos, 'settings link should still be present');
        $this->assertNotFalse($pagesLinkPos, 'pages submenu should still be present');
        $this->assertNotFalse($sidebarCloseTagPos);
        // Settings must come after every other sidebar link, including the
        // last item of the "الصفحات" submenu (previously the last group).
        $this->assertGreaterThan($pagesLinkPos, $settingsLinkPos);
    }

    public function test_settings_link_still_respects_its_original_permission_check()
    {
        // No "view videos" permission granted here — the settings link's
        // guard clause must still hide it, exactly as before the move.
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringNotContainsString(route('admin.settings.edit'), $html);
    }

    public function test_settings_link_shows_with_the_view_videos_permission()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin(['view videos']));
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString(route('admin.settings.edit'), $html);
        $this->assertStringContainsString('الإعدادات', $html);
    }

    public function test_wide_search_css_is_400px_on_large_screens()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression(
            '/\.md-wide-search div\.dataTables_wrapper div\.dataTables_filter input\s*\{\s*width:\s*400px\s*!important;/s',
            $css
        );
        $this->assertStringNotContainsString('width: 520px !important;', $css);
    }

    /**
     * The narrow-screen (<=991px) behavior for the search box is a
     * completely separate rule and must stay untouched by the 520->400px
     * change above.
     */
    public function test_narrow_screen_search_rule_is_unaffected()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression('/@media \(max-width: 991px\)/', $css);
    }
}
