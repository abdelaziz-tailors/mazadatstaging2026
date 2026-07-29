<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\AuctionController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\PackageController;
use App\Http\Controllers\Dashboard\PartnerController;
use App\Http\Controllers\Dashboard\PartnerFinanceController;
use App\Http\Controllers\Dashboard\SellerSubmissionController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\UserSubscriptionController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\Package;
use App\Models\SellerSubmission;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: on every dashboard list page that shows a row of stat
 * cards (Auctions, Orders, Users, Users?user_type=buyer, Packages, partner
 * invoices, user-subscriptions), every card had a real month-over-month (or
 * percent-of-total) trend value EXCEPT the last "total" card, which showed
 * no percentage at all — an inconsistency the user explicitly flagged
 * across several of these pages one at a time. Fixed identically everywhere:
 * a real count of this-calendar-month rows vs the previous calendar month,
 * not a hardcoded number.
 */
class TotalCardTrendTest extends TestCase
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

        foreach (['view videos', 'view users', 'view packages', 'view partners'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function assertHasRealTrend(array $stats): void
    {
        $this->assertArrayHasKey('total_trend_direction', $stats);
        $this->assertArrayHasKey('total_trend_pct', $stats);
        $this->assertContains($stats['total_trend_direction'], ['up', 'down']);
        $this->assertIsFloat($stats['total_trend_pct']);
        $this->assertGreaterThanOrEqual(0.0, $stats['total_trend_pct']);
    }

    public function test_auctions_total_card_has_a_trend()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        LiveVideo::create(['title' => 'Auction', 'created_at' => now()]);
        LiveVideo::create(['title' => 'Auction 2', 'created_at' => now()->subMonthNoOverflow()]);

        $view = (new AuctionController())->index(new Request());
        $this->assertHasRealTrend($view->getData()['stats']);
    }

    public function test_orders_total_card_has_a_trend()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        $buyer = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);
        Order::create([
            'order_number' => 'ORD-TEST-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id, 'buyer_id' => $buyer->id, 'total' => 100,
            'payment_status' => 'unpaid', 'status' => 'pending', 'created_at' => now(),
        ]);

        $view = (new OrderController())->index(new Request());
        $this->assertHasRealTrend($view->getData()['stats']);
    }

    public function test_users_unfiltered_total_card_has_a_trend_and_type_breakdown_percentages()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $view = (new UserController())->index(new Request());
        $stats = $view->getData()['stats'];

        $this->assertHasRealTrend($stats);
        $this->assertArrayHasKey('buyers_pct', $stats);
        $this->assertArrayHasKey('vendors_pct', $stats);
        $this->assertArrayHasKey('sellers_pct', $stats);
    }

    public function test_users_buyer_filtered_total_card_has_a_trend()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $view = (new UserController())->index(new Request(['user_type' => 'buyer']));
        $this->assertHasRealTrend($view->getData()['stats']);
    }

    public function test_packages_total_and_active_cards_have_trends()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        Package::create([
            'name' => json_encode(['ar' => 'باقة']), 'description' => json_encode(['ar' => '']),
            'features' => json_encode(['ar' => []]), 'coin' => 0, 'price' => 0,
            'is_active' => true, 'created_at' => now(),
        ]);

        $view = (new PackageController())->index();
        $stats = $view->getData()['stats'];

        $this->assertHasRealTrend($stats);
        $this->assertArrayHasKey('active_pct', $stats);
    }

    public function test_partner_invoices_total_card_has_a_trend()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $view = (new PartnerFinanceController())->invoices(new Request());
        $this->assertHasRealTrend($view->getData()['stats']);
    }

    public function test_user_subscriptions_total_card_has_a_trend()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $user = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);
        $package = Package::create([
            'name' => json_encode(['ar' => 'باقة']), 'description' => json_encode(['ar' => '']),
            'features' => json_encode(['ar' => []]), 'coin' => 0, 'price' => 0,
        ]);
        UserSubscription::create(['user_id' => $user->id, 'package_id' => $package->id, 'status' => 'approved']);

        $view = (new UserSubscriptionController())->index();
        $this->assertHasRealTrend($view->getData()['stats']);
    }

    public function test_partners_total_active_and_inactive_cards_have_trends()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $partnerUser = User::create([
            'name' => 'Partner', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'vendor', 'gender' => 'male',
            'is_active' => true,
        ]);
        Admin::create([
            'name' => 'Partner Admin', 'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'), 'type' => 'partner', 'user_id' => $partnerUser->id,
            'created_at' => now(),
        ]);

        $view = (new PartnerController())->index();
        $stats = $view->getData()['stats'];

        $this->assertHasRealTrend($stats);
        $this->assertArrayHasKey('active_pct', $stats);
        $this->assertArrayHasKey('inactive_pct', $stats);
        $this->assertArrayHasKey('new_this_month_trend_direction', $stats);
        $this->assertArrayHasKey('new_this_month_trend_pct', $stats);
    }

    public function test_seller_submissions_total_and_breakdown_cards_have_trends()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $partnerUser = User::create([
            'name' => 'Partner', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'vendor', 'gender' => 'male',
        ]);
        SellerSubmission::create(['sheep_type' => 'Sheep', 'status' => 'approved', 'partner_id' => $partnerUser->id, 'created_at' => now()]);

        $view = (new SellerSubmissionController())->index();
        $stats = $view->getData()['stats'];

        $this->assertHasRealTrend($stats);
        $this->assertArrayHasKey('approved_pct', $stats);
        $this->assertArrayHasKey('rejected_pct', $stats);
        $this->assertArrayHasKey('under_review_pct', $stats);
    }

    public function test_pages_render_the_total_cards_trend_markup_without_errors()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $this->assertGreaterThan(0, strlen((new AuctionController())->index(new Request())->render()));
        $this->assertGreaterThan(0, strlen((new OrderController())->index(new Request())->render()));
        $this->assertGreaterThan(0, strlen((new UserController())->index(new Request())->render()));
        $this->assertGreaterThan(0, strlen((new UserController())->index(new Request(['user_type' => 'buyer']))->render()));
        $this->assertGreaterThan(0, strlen((new PackageController())->index()->render()));
        $this->assertGreaterThan(0, strlen((new PartnerFinanceController())->invoices(new Request())->render()));
        $this->assertGreaterThan(0, strlen((new UserSubscriptionController())->index()->render()));
        $this->assertGreaterThan(0, strlen((new PartnerController())->index()->render()));
        $this->assertGreaterThan(0, strlen((new SellerSubmissionController())->index()->render()));
    }
}
