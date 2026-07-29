<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\OrderController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * These hit the controller directly rather than the HTTP route: the admin
 * dashboard's `dashboardLocale` middleware redirects whenever
 * LaravelLocalization's Accept-Language negotiation doesn't resolve to 'ar'
 * (a pre-existing quirk of this app's admin routes, unrelated to the sales
 * "stats brief" feature under test), which makes it unreliable to drive
 * these controllers through the full HTTP stack in tests.
 */
class OrderControllerStatsTest extends TestCase
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

    private function createBuyer(): User
    {
        return User::create([
            'name' => 'Buyer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);
    }

    private function createOrder(string $paymentStatus, string $status): Order
    {
        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        $buyer = $this->createBuyer();

        return Order::create([
            'order_number' => 'ORD-TEST-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 1000,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);
    }

    /**
     * The stats are a global count over a real, shared testing database, so
     * assertions compare before/after deltas instead of exact totals — other
     * already-committed rows outside this test's own transaction would make
     * an exact-total assertion flaky.
     */
    public function test_index_computes_correct_order_stats()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $baseline = Order::query()->selectRaw("
            count(*) as total,
            sum(payment_status = 'unpaid') as pending,
            sum(payment_status = 'paid') as paid,
            sum(status = 'shipping') as shipping
        ")->first();

        $this->createOrder('unpaid', 'pending');
        $this->createOrder('unpaid', 'pending');
        $this->createOrder('paid', 'confirmed');
        $this->createOrder('paid', 'shipping');

        $view = (new OrderController())->index(request());
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->total + 4, $stats['total']);
        $this->assertEquals((int) $baseline->pending + 2, $stats['pending']);
        $this->assertEquals((int) $baseline->paid + 2, $stats['paid']);
        $this->assertEquals((int) $baseline->shipping + 1, $stats['shipping']);
    }

    public function test_index_renders_the_stat_brief_with_the_total_orders_label()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new OrderController())->index(request());

        $this->assertArrayHasKey('stats', $view->getData());
        $this->assertStringContainsString(TranslationHelper::translate('total_orders'), $view->render());
    }
}
