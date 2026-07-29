<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Per explicit request, the dashboard home page's DOM order was aligned to
 * the RTL design reference — in RTL, the first element in the DOM renders
 * rightmost, so the reference's right-to-left reading order must be the
 * literal HTML order:
 *   Row 1 (charts): sales trend -> user registrations -> auctions by status.
 *   Row 2: quick actions (rightmost, next to the sidebar) -> latest
 *   auctions -> latest users.
 * The chart line/area colors were also switched from the old gold/blue mix
 * to the brand green, and the "scheduled" donut slice from blue to gold, to
 * match the reference's palette.
 */
class DashboardHomeLayoutOrderTest extends TestCase
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

    public function test_chart_row_dom_order_is_sales_then_registrations_then_status_donut()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        LiveVideo::create(['title' => 'Auction', 'status' => 'start']);
        User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $html = (new DashboardController())->index()->render();

        $salesPos = strpos($html, 'id="salesChart"');
        $registrationsPos = strpos($html, 'id="registrationsChart"');
        $donutPos = strpos($html, 'id="statusDonutChart"');

        $this->assertNotFalse($salesPos);
        $this->assertNotFalse($registrationsPos);
        $this->assertNotFalse($donutPos);
        $this->assertTrue($salesPos < $registrationsPos, 'sales trend chart should come before registrations chart in the DOM');
        $this->assertTrue($registrationsPos < $donutPos, 'registrations chart should come before the status donut in the DOM');
    }

    public function test_second_row_dom_order_is_quick_actions_then_latest_auctions_then_latest_users()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $html = (new DashboardController())->index()->render();

        $quickActionsPos = strpos($html, 'md-quick-action-grid');
        $latestAuctionsPos = strpos($html, '>' . \App\Helpers\TranslationHelper::translate('latest_auctions') . '<');
        $latestUsersPos = strpos($html, '>' . \App\Helpers\TranslationHelper::translate('latest_users') . '<');

        $this->assertNotFalse($quickActionsPos);
        $this->assertNotFalse($latestAuctionsPos);
        $this->assertNotFalse($latestUsersPos);
        $this->assertTrue($quickActionsPos < $latestAuctionsPos, 'quick actions should come before latest auctions in the DOM');
        $this->assertTrue($latestAuctionsPos < $latestUsersPos, 'latest auctions should come before latest users in the DOM');
    }

    public function test_trend_charts_use_the_brand_green_color_instead_of_the_old_gold_blue_mix()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $html = (new DashboardController())->index()->render();

        $this->assertStringNotContainsString("colors: ['#d3a24a']", $html);
        $this->assertStringNotContainsString("colors: ['#4f9ef8']", $html);
        $this->assertStringContainsString("colors: ['#1f4a38']", $html);
    }

    public function test_status_donut_scheduled_slice_uses_gold_instead_of_blue()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        LiveVideo::create(['title' => 'Auction', 'status' => 'start']);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString("colors: ['#34c38f', '#f1b44c', '#f15b5b']", $html);
        $this->assertStringContainsString('background: var(--md-warning)', $html);
    }
}
