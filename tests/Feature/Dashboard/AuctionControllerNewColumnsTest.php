<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\AuctionController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use App\Models\VideoComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * New Auctions-table columns: start date+time, end date+time, product
 * count, and participation count. Calls the controller directly (rather
 * than the HTTP route) and binds the request into the container, per the
 * established convention for Yajra DataTables controllers in this
 * dashboard — see UserControllerStatsTest for why.
 *
 * start_date/end_date each render as a single two-line cell: the date, and
 * that same date's time stacked underneath it in the same column — not as
 * separate start_time/end_time columns.
 */
class AuctionControllerNewColumnsTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithViewVideosPermission(): Admin
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

    private function createPartnerAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Partner',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);

        $permission = Permission::firstOrCreate(['name' => 'view videos', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    private function rowFor(LiveVideo $liveVideo): array
    {
        $request = Request::create('/admin/auctions/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new AuctionController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $liveVideo->id);
    }

    public function test_start_date_cell_contains_both_the_date_and_the_time_stacked_together()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'date_start_at' => '2026-07-20',
            'time_start_at' => '14:30',
        ]);

        $row = $this->rowFor($liveVideo);

        $this->assertStringContainsString('2026-07-20', $row['start_date']);
        $this->assertStringContainsString('02:30 PM', $row['start_date']);
    }

    public function test_end_date_cell_contains_both_the_date_and_the_time_stacked_together()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'date_end_at' => '2026-07-22',
            'time_end_at' => '09:00',
        ]);

        $row = $this->rowFor($liveVideo);

        $this->assertStringContainsString('2026-07-22', $row['end_date']);
        $this->assertStringContainsString('09:00 AM', $row['end_date']);
    }

    /**
     * /admin/auctions is shared by the super-admin and the partner
     * dashboard — PartnerDashboardScope only scopes which rows a partner
     * sees, never which columns, so this same combined date+time cell
     * applies to both.
     */
    public function test_partner_admin_also_sees_the_combined_start_and_end_date_time_cells()
    {
        $partner = $this->createPartnerAdmin();
        Auth::guard('admin')->setUser($partner);

        $liveVideo = LiveVideo::create([
            'title' => 'Partner Auction',
            'admin_id' => $partner->id,
            'date_start_at' => '2026-08-01',
            'time_start_at' => '16:45',
            'date_end_at' => '2026-08-02',
            'time_end_at' => '07:15',
        ]);

        $row = $this->rowFor($liveVideo);

        $this->assertNotNull($row);
        $this->assertStringContainsString('2026-08-01', $row['start_date']);
        $this->assertStringContainsString('04:45 PM', $row['start_date']);
        $this->assertStringContainsString('2026-08-02', $row['end_date']);
        $this->assertStringContainsString('07:15 AM', $row['end_date']);
    }

    public function test_start_date_and_end_date_fall_back_to_a_dash_when_not_set()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $liveVideo = LiveVideo::create(['title' => 'Auction, no schedule yet']);

        $row = $this->rowFor($liveVideo);

        $this->assertEquals('-', $row['start_date']);
        $this->assertEquals('-', $row['end_date']);
    }

    public function test_date_cell_omits_the_time_line_when_only_the_date_is_set()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'date_start_at' => '2026-07-20',
            'time_start_at' => null,
        ]);

        $row = $this->rowFor($liveVideo);

        $this->assertStringContainsString('2026-07-20', $row['start_date']);
        $this->assertStringNotContainsString('AM', $row['start_date']);
        $this->assertStringNotContainsString('PM', $row['start_date']);
    }

    public function test_products_count_reflects_the_auctions_own_live_video_items_only()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $myAuction = LiveVideo::create(['title' => 'Mine']);
        $otherAuction = LiveVideo::create(['title' => 'Not mine']);

        LiveVideoItem::create(['live_video_id' => $myAuction->id]);
        LiveVideoItem::create(['live_video_id' => $myAuction->id]);
        LiveVideoItem::create(['live_video_id' => $myAuction->id]);
        LiveVideoItem::create(['live_video_id' => $otherAuction->id]);

        $row = $this->rowFor($myAuction);

        $this->assertEquals(3, $row['products_count']);
    }

    public function test_participations_count_is_the_distinct_bidder_count_for_that_auction_only()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $myAuction = LiveVideo::create(['title' => 'Mine']);
        $otherAuction = LiveVideo::create(['title' => 'Not mine']);

        $bidderA = User::create(['name' => 'A', 'phone' => '01' . random_int(100000000, 999999999), 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male']);
        $bidderB = User::create(['name' => 'B', 'phone' => '01' . random_int(100000000, 999999999), 'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male']);

        VideoComment::create(['video_id' => $myAuction->id, 'user_id' => $bidderA->id, 'comment' => '100']);
        // Same bidder placing a second bid on my auction must only count once.
        VideoComment::create(['video_id' => $myAuction->id, 'user_id' => $bidderA->id, 'comment' => '150']);
        VideoComment::create(['video_id' => $myAuction->id, 'user_id' => $bidderB->id, 'comment' => '120']);
        // A bid on a different auction must not count towards this one.
        VideoComment::create(['video_id' => $otherAuction->id, 'user_id' => $bidderA->id, 'comment' => '50']);

        $row = $this->rowFor($myAuction);

        $this->assertEquals(2, $row['participations_count']);
    }

    public function test_participations_count_is_zero_when_no_one_has_bid()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $liveVideo = LiveVideo::create(['title' => 'No bids yet']);

        $row = $this->rowFor($liveVideo);

        $this->assertEquals(0, $row['participations_count']);
    }
}
