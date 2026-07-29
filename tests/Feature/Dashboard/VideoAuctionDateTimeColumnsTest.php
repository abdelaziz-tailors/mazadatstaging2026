<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\VideoController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * /admin/videos (the auctions list, shared by the super-admin and the
 * partner dashboard) only showed a single "auction_time" column — the raw
 * start date, no time, and no end date at all. Per explicit request, this
 * now shows both the auction's start date+time and end date+time, each as
 * a two-line cell (date, then the time with Arabic ص/م — date_start_at/
 * date_end_at and time_start_at/time_end_at are stored as separate date/
 * time columns and combined here for display only).
 */
class VideoAuctionDateTimeColumnsTest extends TestCase
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

    private function createPartnerAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Partner',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
    }

    private function callGetData(Admin $admin): array
    {
        Auth::guard('admin')->setUser($admin);

        $request = Request::create('/admin/videos/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new VideoController())->get_data($request);

        return json_decode($response->getContent(), true)['data'];
    }

    public function test_get_data_returns_start_and_end_date_with_arabic_am_pm_time()
    {
        $admin = $this->createAdmin();
        $liveVideo = LiveVideo::create([
            'title' => 'Auction', 'title_ar' => 'مزاد',
            'date_start_at' => '2026-06-10', 'time_start_at' => '09:30:00',
            'date_end_at' => '2026-06-11', 'time_end_at' => '20:00:00',
        ]);

        $data = $this->callGetData($admin);
        $row = collect($data)->firstWhere('id', $liveVideo->id);

        $this->assertStringContainsString('2026/06/10', $row['start_at']);
        $this->assertStringContainsString('09:30 ص', $row['start_at']);
        $this->assertStringContainsString('2026/06/11', $row['end_at']);
        $this->assertStringContainsString('08:00 م', $row['end_at']);
    }

    public function test_get_data_handles_a_missing_time_gracefully()
    {
        $admin = $this->createAdmin();
        $liveVideo = LiveVideo::create([
            'title' => 'Auction', 'title_ar' => 'مزاد',
            'date_start_at' => '2026-06-10', 'time_start_at' => null,
            'date_end_at' => null, 'time_end_at' => null,
        ]);

        $data = $this->callGetData($admin);
        $row = collect($data)->firstWhere('id', $liveVideo->id);

        $this->assertStringContainsString('2026/06/10', $row['start_at']);
        $this->assertStringNotContainsString('ص', $row['start_at']);
        $this->assertStringNotContainsString('م', $row['start_at']);
        $this->assertStringContainsString('—', $row['end_at']);
    }

    public function test_index_page_shows_the_start_and_end_date_column_headers_instead_of_the_old_single_column()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VideoController())->index(new Request())->render();

        $this->assertStringContainsString(TranslationHelper::translate('date_start'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('date_end'), $html);
        $this->assertStringContainsString("data: 'start_at'", $html);
        $this->assertStringContainsString("data: 'end_at'", $html);
    }

    public function test_partner_admin_also_sees_the_start_and_end_date_columns()
    {
        $partner = $this->createPartnerAdmin();
        $liveVideo = LiveVideo::create([
            'title' => 'Partner Auction', 'title_ar' => 'مزاد الشريك',
            'admin_id' => $partner->id,
            'date_start_at' => '2026-07-01', 'time_start_at' => '14:15:00',
            'date_end_at' => '2026-07-02', 'time_end_at' => '06:45:00',
        ]);

        $data = $this->callGetData($partner);
        $row = collect($data)->firstWhere('id', $liveVideo->id);

        $this->assertNotNull($row);
        $this->assertStringContainsString('2026/07/01', $row['start_at']);
        $this->assertStringContainsString('02:15 م', $row['start_at']);
        $this->assertStringContainsString('2026/07/02', $row['end_at']);
        $this->assertStringContainsString('06:45 ص', $row['end_at']);
    }
}
