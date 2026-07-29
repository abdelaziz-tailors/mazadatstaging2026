<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PartnerFinanceController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Hits the controller directly rather than the HTTP route — see the note in
 * OrderControllerStatsTest for why (a pre-existing dashboard-locale redirect
 * quirk unrelated to this feature).
 */
class PartnerFinanceControllerStatsTest extends TestCase
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
     * Not a partner admin (type='admin'), so PartnerDashboardScope::scopeOrders
     * is a no-op and stats are a global count over a real, shared testing
     * database — assertions compare before/after deltas instead of exact totals.
     */
    public function test_invoices_computes_correct_order_stats()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $baseline = Order::query()->selectRaw("
            count(*) as total,
            sum(payment_status = 'unpaid') as pending,
            sum(payment_status = 'paid') as paid,
            sum(status = 'shipping') as shipping
        ")->first();

        $this->createOrder('unpaid', 'pending');
        $this->createOrder('paid', 'confirmed');
        $this->createOrder('paid', 'shipping');

        $view = (new PartnerFinanceController())->invoices(new Request());
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->total + 3, $stats['total']);
        $this->assertEquals((int) $baseline->pending + 1, $stats['pending']);
        $this->assertEquals((int) $baseline->paid + 2, $stats['paid']);
        $this->assertEquals((int) $baseline->shipping + 1, $stats['shipping']);
    }

    public function test_invoices_renders_the_stat_brief_with_the_total_invoices_label()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new PartnerFinanceController())->invoices(new Request());

        $this->assertArrayHasKey('stats', $view->getData());
        $this->assertStringContainsString(TranslationHelper::translate('total_invoices'), $view->render());
    }

    public function test_invoices_actions_column_uses_a_view_icon_not_a_text_button()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $order = $this->createOrder('unpaid', 'pending');

        $html = (new PartnerFinanceController())->invoices(new Request())->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString(route('admin.orders.edit', $order->id), $html);
        $this->assertStringNotContainsString('btn-outline-primary', $html);
    }

    public function test_invoices_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->invoices(new Request())->render();

        $this->assertStringContainsString(TranslationHelper::translate('search_invoices_placeholder'), $html);
    }

    public function test_invoices_search_filters_by_order_number()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $target = $this->createOrder('unpaid', 'pending');
        $other = $this->createOrder('paid', 'confirmed');

        $view = (new PartnerFinanceController())->invoices(new Request(['search' => $target->order_number]));
        $invoices = $view->getData()['invoices'];

        $this->assertTrue($invoices->contains('id', $target->id));
        $this->assertFalse($invoices->contains('id', $other->id));
    }

    public function test_invoices_search_filters_by_buyer_name()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $buyer = $this->createBuyer();
        $buyer->update(['name' => 'Unique Buyer Name ' . random_int(100000, 999999)]);

        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        $target = Order::create([
            'order_number' => 'ORD-TEST-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 500,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
        $other = $this->createOrder('paid', 'confirmed');

        $view = (new PartnerFinanceController())->invoices(new Request(['search' => $buyer->name]));
        $invoices = $view->getData()['invoices'];

        $this->assertTrue($invoices->contains('id', $target->id));
        $this->assertFalse($invoices->contains('id', $other->id));
    }

    public function test_invoices_search_with_no_matches_returns_an_empty_list()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $this->createOrder('unpaid', 'pending');

        $view = (new PartnerFinanceController())->invoices(new Request(['search' => 'no-such-order-xyz']));
        $invoices = $view->getData()['invoices'];

        $this->assertCount(0, $invoices);
    }
}
