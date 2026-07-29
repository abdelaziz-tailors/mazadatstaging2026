<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\SellerSubmission;
use App\Models\User\User;
use App\Models\UserSubscription;
use App\Support\AdminNotificationsFeed;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The header bell dropdown used to be two hardcoded static links
 * ("seller submissions" / "manage subscriptions"). Per explicit request it
 * now shows the real 5 most-recently-created rows across the platform's
 * real event sources (seller submissions, subscription requests, orders,
 * auctions), merged and sorted by created_at, each linking straight to that
 * record's own admin page — see App\Support\AdminNotificationsFeed. There is
 * no persisted "admin notifications" table; this is a live merge, same
 * spirit as the header's existing pending-review badge count composer.
 */
class HeaderNotificationsFeedTest extends TestCase
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

    private function createPartnerAdmin(User $partnerUser): Admin
    {
        return Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'user_id' => $partnerUser->id,
        ]);
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    private function createSellerSubmission(User $partnerUser, array $overrides = []): SellerSubmission
    {
        return SellerSubmission::create(array_merge([
            'sheep_type' => 'Najdi',
            'age' => '1',
            'expected_price' => 500,
            'status' => 'pending',
            'partner_id' => $partnerUser->id,
        ], $overrides));
    }

    private function createLiveVideo(array $overrides = []): LiveVideo
    {
        return LiveVideo::create(array_merge(['title' => 'Auction'], $overrides));
    }

    private function createOrder(LiveVideo $liveVideo, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $this->createUser()->id,
        ], $overrides));
    }

    public function test_feed_merges_all_four_sources_sorted_by_recency_capped_at_the_limit()
    {
        // The feed pulls the true latest rows across the whole table with no
        // scoping hook (by design — it's a global "what just happened"
        // feed), so leftover rows from other tests/manual sessions in this
        // shared dev DB could otherwise outrank these deliberately-backdated
        // fixtures and break the strict ordering assertions below.
        SellerSubmission::query()->forceDelete();
        UserSubscription::query()->forceDelete();
        Order::query()->forceDelete();
        LiveVideo::query()->forceDelete();

        $partner = $this->createUser(['user_type' => 'vendor']);

        $submission = $this->createSellerSubmission($partner, ['created_at' => now()->subMinutes(5)]);
        $subscription = UserSubscription::create(['user_id' => $this->createUser()->id, 'status' => 'pending', 'created_at' => now()->subMinutes(4)]);
        $liveVideo = $this->createLiveVideo(['created_at' => now()->subMinutes(3)]);
        $order = $this->createOrder($liveVideo, ['created_at' => now()->subMinutes(2)]);
        $newestAuction = $this->createLiveVideo(['title' => 'Newest', 'created_at' => now()->subMinute()]);

        $feed = AdminNotificationsFeed::latest(5);

        $this->assertCount(5, $feed);
        // Newest first.
        $this->assertEquals('auction', $feed[0]['type']);
        $this->assertEquals($newestAuction->id, $this->extractId($feed[0]['url']));
        $this->assertEquals('order', $feed[1]['type']);
        $this->assertEquals('auction', $feed[2]['type']);
        $this->assertEquals('user_subscription', $feed[3]['type']);
        $this->assertEquals('seller_submission', $feed[4]['type']);
    }

    public function test_feed_items_link_to_the_records_own_admin_show_page()
    {
        $partner = $this->createUser(['user_type' => 'vendor']);
        $submission = $this->createSellerSubmission($partner);

        $feed = AdminNotificationsFeed::latest(5);
        $submissionItem = $feed->firstWhere('type', 'seller_submission');

        $this->assertNotNull($submissionItem);
        $this->assertEquals(route('admin.seller-submissions.show', $submission->id), $submissionItem['url']);
    }

    public function test_super_admin_sees_seller_submissions_and_orders_from_every_partner()
    {
        $partnerA = $this->createUser(['user_type' => 'vendor']);
        $partnerB = $this->createUser(['user_type' => 'vendor']);
        Auth::guard('admin')->setUser($this->createSuperAdmin());

        $this->createSellerSubmission($partnerA);
        $this->createSellerSubmission($partnerB);

        $feed = AdminNotificationsFeed::latest(5);
        $submissionCount = $feed->where('type', 'seller_submission')->count();

        $this->assertGreaterThanOrEqual(2, $submissionCount);
    }

    public function test_partner_admin_only_sees_their_own_seller_submissions()
    {
        $myPartnerUser = $this->createUser(['user_type' => 'vendor']);
        $otherPartnerUser = $this->createUser(['user_type' => 'vendor']);
        $me = $this->createPartnerAdmin($myPartnerUser);
        Auth::guard('admin')->setUser($me);

        $mine = $this->createSellerSubmission($myPartnerUser, ['sheep_type' => 'Mine']);
        $this->createSellerSubmission($otherPartnerUser, ['sheep_type' => 'NotMine']);

        $feed = AdminNotificationsFeed::latest(5);
        $submissionItems = $feed->where('type', 'seller_submission');

        $this->assertCount(1, $submissionItems);
        $this->assertEquals(route('admin.seller-submissions.show', $mine->id), $submissionItems->first()['url']);
    }

    public function test_header_dropdown_renders_real_notification_items_with_links()
    {
        $partner = $this->createUser(['user_type' => 'vendor']);
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $submission = $this->createSellerSubmission($partner);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-notif-item', $html);
        $this->assertStringContainsString(route('admin.seller-submissions.show', $submission->id), $html);
        $this->assertStringContainsString(TranslationHelper::translate('new_seller_submission'), $html);
    }

    /**
     * Each item shows a relative "x minutes/hours ago" timestamp (per
     * explicit request) so the admin can tell how fresh it is at a glance.
     */
    public function test_header_dropdown_shows_a_relative_time_for_each_item()
    {
        $partner = $this->createUser(['user_type' => 'vendor']);
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $this->createSellerSubmission($partner, ['created_at' => now()->subHours(2)]);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-notif-item-time', $html);
        $this->assertStringContainsString(now()->subHours(2)->diffForHumans(), $html);
    }

    /**
     * Per explicit request, the pending-review count next to the bell icon
     * dropped its pill/circle background — it's just the number in red text
     * now, no "badge rounded-pill bg-danger" wrapper.
     */
    public function test_notif_badge_has_no_pill_background_just_the_plain_count()
    {
        $admin = $this->createSuperAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $partner = $this->createUser(['user_type' => 'vendor']);
        $this->createSellerSubmission($partner);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-notif-badge', $html);
        $this->assertStringNotContainsString('badge rounded-pill bg-danger md-notif-badge', $html);

        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');
        $this->assertMatchesRegularExpression('/\.md-notif-badge\s*\{[^}]*background:\s*none;/s', $css);
    }

    /**
     * Regression guard: a legacy rule ("user-menu.nav > li > a i") sets
     * line-height:60px on the bell icon to vertically center it inside the
     * full-height header row — that same rule also stretched .md-notif-
     * toggle's own box to 60px tall, so the badge's "top" offset (measured
     * from that tall box) landed up near the header's top edge instead of
     * next to the actual bell glyph. Fixed by positioning the badge against
     * a tight wrapper around just the icon instead, with its line-height
     * forced back to normal.
     */
    public function test_notif_badge_is_positioned_against_a_tight_icon_wrapper_not_the_tall_toggle_box()
    {
        $admin = $this->createSuperAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $partner = $this->createUser(['user_type' => 'vendor']);
        $this->createSellerSubmission($partner);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-notif-icon-wrap', $html);
        // The badge must be nested inside the icon wrapper, not a sibling
        // directly under .md-notif-toggle.
        $wrapPos = strpos($html, 'md-notif-icon-wrap');
        $badgePos = strpos($html, 'md-notif-badge');
        $iconClosePos = strpos($html, '</i>', $wrapPos);
        $this->assertNotFalse($wrapPos);
        $this->assertNotFalse($badgePos);
        $this->assertNotFalse($iconClosePos);
        $this->assertTrue($wrapPos < $badgePos && $badgePos > $iconClosePos - 200, 'badge must sit right after the icon inside the wrapper');

        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');
        $this->assertMatchesRegularExpression('/\.md-notif-icon-wrap i\s*\{[^}]*line-height:\s*1\s*!important;/s', $css);
    }

    public function test_header_dropdown_shows_empty_state_when_there_is_nothing_at_all()
    {
        SellerSubmission::query()->forceDelete();
        UserSubscription::query()->forceDelete();
        Order::query()->forceDelete();
        LiveVideo::query()->forceDelete();

        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('no_notifications'), $html);
    }

    private function extractId(string $url): int
    {
        preg_match('/(\d+)$/', $url, $matches);

        return (int) ($matches[1] ?? 0);
    }
}
