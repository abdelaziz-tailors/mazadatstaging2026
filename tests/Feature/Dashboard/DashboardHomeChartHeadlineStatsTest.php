<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Per explicit request, the "تسجيلات المستخدمين" / "اتجاه المبيعات" chart
 * cards now show a headline number + a period-over-period trend badge
 * (e.g. "46 مستخدم جديد" + "15% ▲ عن الفترة السابقة") above the chart,
 * matching a design reference. The trend compares the selected "last N
 * days" window against the N days immediately before it — a real DB
 * computation via DashboardController::periodOverPeriodTrend(), distinct
 * from the calendar-month trend used by the top stat-grid cards.
 *
 * Also covers: the quick-action "إضافة قسم جديد" icon color (was blue,
 * now green per the reference), and the sidebar's "wallet" label typo fix
 * (كانت "المحافظ"، أصبحت "المحفظة").
 */
class DashboardHomeChartHeadlineStatsTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    public function test_registrations_headline_matches_a_direct_count_of_new_users_in_the_period()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        User::create([
            'name' => 'Recent Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $expectedCount = User::whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])->count();

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-chart-stat-value">' . number_format($expectedCount) . '<', $html);
        $this->assertStringContainsString(TranslationHelper::translate('new_users'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('vs_previous_period'), $html);
    }

    public function test_sales_headline_matches_a_direct_sum_of_finished_price_in_the_period()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $auction = LiveVideo::create(['title' => 'Auction']);
        LiveVideoItem::create(['live_video_id' => $auction->id, 'finished_price' => 500]);

        $expectedSum = (float) LiveVideoItem::whereNotNull('finished_price')
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->sum('finished_price');

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-chart-stat-value">' . number_format($expectedSum, 0) . '<', $html);
        $this->assertStringContainsString(TranslationHelper::translate('gross_sales'), $html);
    }

    public function test_trend_direction_class_and_arrow_reflect_an_increase()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        User::create([
            'name' => 'Recent Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-chart-stat-trend up', $html);
        $this->assertStringContainsString('fa-solid fa-arrow-up', $html);
    }

    /**
     * Per explicit follow-up request, all 4 quick-action icons use the same
     * green (stat-icon-success) — the earlier gold/purple/blue mix was
     * inconsistent with the reference design.
     */
    public function test_all_quick_action_icons_use_the_same_green_success_color()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $gridPos = strpos($html, 'md-quick-action-grid');
        $this->assertNotFalse($gridPos);
        $gridBlock = substr($html, $gridPos, 1600);

        $this->assertSame(4, substr_count($gridBlock, 'stat-icon stat-icon-success'));
        $this->assertStringNotContainsString('stat-icon-warning', $gridBlock);
        $this->assertStringNotContainsString('stat-icon-info', $gridBlock);
        $this->assertStringNotContainsString('stat-icon-purple', $gridBlock);
    }

    public function test_sidebar_wallet_label_is_singular_al_mahfaza_not_al_mahafiz()
    {
        $admin = $this->createSuperAdmin();
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view videos', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('المحفظة', $html);
        $this->assertStringNotContainsString('المحافظ<', $html);
    }
}
