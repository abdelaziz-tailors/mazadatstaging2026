<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Per explicit request, the dashboard home page's "أحدث المزادات" panel was
 * turned into a proper table (title, seller/partner, end date, status,
 * view) reusing the existing partner relation from the Auctions list page,
 * instead of the previous simple row-list with just a title + creation date.
 */
class DashboardLatestAuctionsTableTest extends TestCase
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

    public function test_latest_auctions_table_shows_the_real_partner_name_end_date_and_view_link()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $partner = Admin::create([
            'name' => 'مزرعة العتيبي',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);

        $auction = LiveVideo::create([
            'title_ar' => 'مزاد أغنام النجدية',
            'admin_id' => $partner->id,
            'date_end_at' => '2026-08-01',
            'time_end_at' => '08:00:00',
            'status' => 'start',
        ]);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('مزاد أغنام النجدية', $html);
        $this->assertStringContainsString('مزرعة العتيبي', $html);
        $this->assertStringContainsString('2026/08/01', $html);
        $this->assertStringContainsString(route('admin.auctions.show', $auction->id), $html);
        $this->assertStringContainsString(TranslationHelper::translate('Auctions Title'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('date_end'), $html);
    }

    public function test_latest_auctions_table_has_a_dedicated_image_column_before_the_title_column()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        LiveVideo::create(['title_ar' => 'مزاد منفصل', 'status' => 'start']);

        $html = (new DashboardController())->index()->render();

        $tablePos = strpos($html, TranslationHelper::translate('latest_auctions'));
        $this->assertNotFalse($tablePos);
        $tableBlock = substr($html, $tablePos, 1600);

        // The header row has an empty <th></th> for the image column, ahead
        // of the title column header.
        $this->assertMatchesRegularExpression('/<th><\/th>\s*<th>' . preg_quote(TranslationHelper::translate('Auctions Title'), '/') . '/', $tableBlock);

        // The first data cell (right after <tbody>) only wraps the avatar
        // placeholder — the title text sits in the following <td>, not this one.
        $tbodyPos = strpos($tableBlock, '<tbody>');
        $this->assertNotFalse($tbodyPos);
        $firstTdPos = strpos($tableBlock, '<td>', $tbodyPos);
        $this->assertNotFalse($firstTdPos);
        $firstTdEnd = strpos($tableBlock, '</td>', $firstTdPos);
        $firstTdContent = substr($tableBlock, $firstTdPos, $firstTdEnd - $firstTdPos);
        $this->assertStringContainsString('md-avatar', $firstTdContent);
        $this->assertStringNotContainsString('fw-semibold', $firstTdContent);
    }

    public function test_latest_auctions_table_falls_back_to_dash_when_partner_is_missing()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        LiveVideo::create([
            'title_ar' => 'مزاد بدون بائع',
            'admin_id' => null,
            'status' => 'start',
        ]);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('مزاد بدون بائع', $html);
    }

    /**
     * Per explicit follow-up request, both "latest" panels were trimmed from
     * 5 (users) / 4 (partner auctions) down to a uniform latest-3.
     */
    public function test_latest_auctions_and_latest_users_are_capped_at_three()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        foreach (range(1, 5) as $i) {
            LiveVideo::create(['title_ar' => "مزاد {$i}", 'status' => 'start']);
        }
        foreach (range(1, 5) as $i) {
            \App\Models\User\User::create([
                'name' => "Buyer {$i}", 'phone' => '01' . random_int(100000000, 999999999),
                'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
            ]);
        }

        $view = (new DashboardController())->index();
        $data = $view->getData();

        $this->assertCount(3, $data['latestAuctions']);
        $this->assertCount(3, $data['latestUsers']);
    }

    public function test_empty_state_is_shown_when_there_are_no_auctions()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        LiveVideo::query()->delete();

        $html = view('dashboard.home', [
            'reports' => [],
            'registrationsChart' => ['labels' => ['1 Jan'], 'values' => [0]],
            'salesChart' => ['labels' => ['1 Jan'], 'values' => [0]],
            'statusChart' => ['active' => 0, 'scheduled' => 0, 'ended' => 0],
            'latestUsers' => collect(),
            'latestAuctions' => collect(),
            'registrationsTrend' => ['direction' => 'up', 'pct' => 0.0, 'value' => 0.0],
            'salesTrend' => ['direction' => 'up', 'pct' => 0.0, 'value' => 0.0],
            'days' => 30,
            'pendingReviewCount' => 0,
        ])->render();

        $this->assertStringContainsString(TranslationHelper::translate('nothing_found'), $html);
    }
}
