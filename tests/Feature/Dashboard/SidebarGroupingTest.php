<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The admin sidebar used to be one flat, ungrouped list under a single
 * "القائمة الرئيسية" heading. Per explicit request (matching a reference
 * design), it's now split into labeled sections — "platform_management"
 * and "content_and_categorization" — while keeping this app's own actual
 * routes (the reference's links don't apply here). The Categories/Colors/
 * Ages links were previously built (controllers/routes/views all already
 * existed and work — their own authorization checks are commented out
 * app-wide) but never linked from any navigation at all; they're now
 * reachable from the new content group.
 */
class SidebarGroupingTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach ([
            'view admins', 'view roles', 'view users', 'view vendors',
            'view partners', 'view videos', 'view packages', 'edit page',
        ] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_sidebar_has_two_named_group_headings_plus_the_main_one()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertEquals(3, substr_count($html, 'class="menu-title"'));
        $this->assertStringContainsString(TranslationHelper::translate('platform_management'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('content_and_categorization'), $html);
    }

    /**
     * Categories/colors were later hidden from the sidebar entirely per
     * explicit request (their controllers/routes/views still exist and
     * work — this is a navigation-only change). Ages stays linked.
     */
    public function test_sidebar_links_to_ages_but_not_categories_or_colors()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString(route('admin.ages.index'), $html);
        // Loose substring checks on the index routes would false-positive
        // against the dashboard home page's unrelated "add new category"
        // quick-action button (which links to admin.categories.create,
        // whose URL starts with the same string as admin.categories.index)
        // — check for an exact href instead, plus the sidebar links' own
        // distinct icon classes as a second signal.
        $this->assertDoesNotMatchRegularExpression(
            '/href="' . preg_quote(route('admin.categories.index'), '/') . '"/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/href="' . preg_quote(route('admin.colors.index'), '/') . '"/',
            $html
        );
        $this->assertStringNotContainsString('fa-solid fa-layer-group', $html);
        $this->assertStringNotContainsString('fa-solid fa-palette', $html);
    }

    public function test_content_group_links_appear_after_the_content_heading_and_before_it_in_platform_group()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $contentHeadingPos = strpos($html, TranslationHelper::translate('content_and_categorization'));
        $agesLinkPos = strpos($html, route('admin.ages.index'));
        $platformHeadingPos = strpos($html, TranslationHelper::translate('platform_management'));

        $this->assertNotFalse($contentHeadingPos);
        $this->assertNotFalse($agesLinkPos);
        $this->assertNotFalse($platformHeadingPos);
        $this->assertLessThan($contentHeadingPos, $platformHeadingPos);
        $this->assertLessThan($agesLinkPos, $contentHeadingPos);
    }
}
