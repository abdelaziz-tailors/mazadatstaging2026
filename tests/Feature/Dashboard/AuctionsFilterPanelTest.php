<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\AuctionController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the auctions-page filter panel added on top of
 * AuctionController::get_data(): status (in-progress/upcoming/archived) and
 * date range filters — the same pattern already built for the buyers/vendors
 * pages, built from real, already-stored columns (status/date_start_at), no
 * new schema. "Upcoming" reuses the exact same bucket logic already used by
 * the stat cards (null status, or any status other than start/end).
 */
class AuctionsFilterPanelTest extends TestCase
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

        $permission = Permission::firstOrCreate(['name' => 'view videos', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    private function callGetData(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $request = Request::create('/admin/auctions/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $params));
        app()->instance('request', $request);

        $response = (new AuctionController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    public function test_filter_status_in_progress_only_returns_start_status()
    {
        $inProgress = LiveVideo::create(['title' => 'A', 'status' => 'start']);
        $ended = LiveVideo::create(['title' => 'B', 'status' => 'end']);
        $upcoming = LiveVideo::create(['title' => 'C', 'status' => null]);

        $ids = $this->callGetData(['filter_status' => 'start']);

        $this->assertTrue($ids->contains($inProgress->id));
        $this->assertFalse($ids->contains($ended->id));
        $this->assertFalse($ids->contains($upcoming->id));
    }

    public function test_filter_status_archived_only_returns_end_status()
    {
        $inProgress = LiveVideo::create(['title' => 'A', 'status' => 'start']);
        $ended = LiveVideo::create(['title' => 'B', 'status' => 'end']);

        $ids = $this->callGetData(['filter_status' => 'end']);

        $this->assertFalse($ids->contains($inProgress->id));
        $this->assertTrue($ids->contains($ended->id));
    }

    public function test_filter_status_upcoming_excludes_start_and_end()
    {
        $inProgress = LiveVideo::create(['title' => 'A', 'status' => 'start']);
        $ended = LiveVideo::create(['title' => 'B', 'status' => 'end']);
        $upcomingNull = LiveVideo::create(['title' => 'C', 'status' => null]);
        $upcomingOther = LiveVideo::create(['title' => 'D', 'status' => 'scheduled']);

        $ids = $this->callGetData(['filter_status' => 'upcoming']);

        $this->assertFalse($ids->contains($inProgress->id));
        $this->assertFalse($ids->contains($ended->id));
        $this->assertTrue($ids->contains($upcomingNull->id));
        $this->assertTrue($ids->contains($upcomingOther->id));
    }

    public function test_filter_date_range_excludes_auctions_outside_the_range()
    {
        $inRange = LiveVideo::create(['title' => 'A', 'date_start_at' => now()->subDays(5)->toDateString()]);
        $outOfRange = LiveVideo::create(['title' => 'B', 'date_start_at' => now()->subDays(30)->toDateString()]);

        $ids = $this->callGetData([
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_combined_status_and_date_filters_apply_together_as_an_intersection()
    {
        $match = LiveVideo::create(['title' => 'A', 'status' => 'start', 'date_start_at' => now()->subDays(5)->toDateString()]);
        $wrongStatus = LiveVideo::create(['title' => 'B', 'status' => 'end', 'date_start_at' => now()->subDays(5)->toDateString()]);
        $wrongDate = LiveVideo::create(['title' => 'C', 'status' => 'start', 'date_start_at' => now()->subDays(30)->toDateString()]);

        $ids = $this->callGetData([
            'filter_status' => 'start',
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
        $this->assertFalse($ids->contains($wrongDate->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $auction = LiveVideo::create(['title' => 'A']);

        $ids = $this->callGetData([]);

        $this->assertTrue($ids->contains($auction->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_auctions_index_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new AuctionController())->index(new Request());
        $html = $view->render();

        $this->assertStringContainsString('id="auctionsFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
        $this->assertStringContainsString('md-wide-search', $html);
    }
}
