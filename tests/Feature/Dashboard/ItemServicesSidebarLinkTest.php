<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The "خدمات القطعة" (item services) feature — a full CRUD page for extra
 * paid services attachable to auction pieces, already used by the wallet
 * settlement calculation — had a working controller/routes/view all along,
 * but was never linked from either sidebar (same latent-feature pattern as
 * categories/colors/ages before SidebarGroupingTest). It's linked from both:
 * the super-admin sidebar and the partner/subscriber sidebar (grouped near
 * seller submissions, since it's the same item/piece-level concern).
 *
 * On the super-admin sidebar it originally landed in the
 * "content_and_categorization" group alongside categories/colors/ages, then
 * moved (per a later explicit request) to sit right after the
 * "الاشتراكات" (subscriptions) submenu instead — still its own standalone
 * link, not nested inside that submenu.
 */
class ItemServicesSidebarLinkTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    private function createPartnerAdmin(): Admin
    {
        $user = User::create([
            'name' => 'Partner User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);

        return Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_sidebar_links_to_item_services()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString(route('admin.item-services.index'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('item_services'), $html);
    }

    /**
     * Must sit right after the "الاشتراكات" (subscriptions) submenu and
     * before the "content_and_categorization" heading — its own standalone
     * link, not nested inside the subscriptions submenu.
     */
    public function test_admin_sidebar_item_services_link_sits_after_subscriptions_and_before_the_content_group()
    {
        $admin = $this->createSuperAdmin();
        foreach (['view packages'] as $name) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $subscriptionsLinkPos = strpos($html, route('admin.user-subscriptions.index'));
        $itemServicesLinkPos = strpos($html, route('admin.item-services.index'));
        $contentHeadingPos = strpos($html, TranslationHelper::translate('content_and_categorization'));

        $this->assertNotFalse($subscriptionsLinkPos);
        $this->assertNotFalse($itemServicesLinkPos);
        $this->assertNotFalse($contentHeadingPos);
        $this->assertLessThan($itemServicesLinkPos, $subscriptionsLinkPos);
        $this->assertLessThan($contentHeadingPos, $itemServicesLinkPos);
    }

    public function test_partner_subscriber_sidebar_links_to_item_services()
    {
        Auth::guard('admin')->setUser($this->createPartnerAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString(route('admin.item-services.index'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('item_services'), $html);
    }
}
