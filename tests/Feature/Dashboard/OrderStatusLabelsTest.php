<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\PartnerFinanceController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Per explicit request: the order status wording on /admin/orders/{id}/edit
 * and on the partner's own invoices page (/admin/partner-finance/invoices)
 * must match the exact Arabic text the mobile app's own order-tracking
 * screen shows for each status — they had drifted apart (e.g. dashboard
 * said "مؤكد" for "confirmed" while the app says "تم التأكيد").
 *
 * Both pages render these labels via TranslationHelper::translate($status)
 * with the same raw enum keys (pending/confirmed/preparation/
 * ready_for_delivery/shipping/delivered), so the fix is entirely in
 * resources/lang/ar/app.php — no controller/view logic changed. Fixing the
 * shared keys there automatically covers every consumer at once.
 */
class OrderStatusLabelsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * These controllers are called directly (bypassing the HTTP route/
     * LaravelLocalization middleware that normally sets the locale from the
     * /ar/admin/... URL prefix in production), so the locale must be set
     * explicitly — otherwise it falls back to config('app.locale') = 'en'.
     */
    protected function setUp(): void
    {
        parent::setUp();
        App::setLocale('ar');
    }

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

    private function createOrder(string $status): Order
    {
        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        $buyer = $this->createBuyer();

        return Order::create([
            'order_number' => 'ORD-TEST-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 1000,
            'payment_status' => 'unpaid',
            'status' => $status,
        ]);
    }

    /**
     * @return array<string, string> status value => the mobile app's exact wording
     */
    private function expectedAppWording(): array
    {
        return [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'تم التأكيد',
            'preparation' => 'قيد التحضير',
            'ready_for_delivery' => 'جاهز للتوصيل',
            'shipping' => 'في الطريق',
            'delivered' => 'تم التوصيل',
        ];
    }

    public function test_translation_keys_match_the_mobile_apps_exact_wording()
    {
        foreach ($this->expectedAppWording() as $status => $expectedText) {
            $this->assertEquals($expectedText, TranslationHelper::translate($status, 'ar'), "Mismatch for status '{$status}'");
        }
    }

    public function test_order_edit_page_status_dropdown_uses_the_apps_wording_for_every_status()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        $order = $this->createOrder('pending');

        $html = (new OrderController())->edit($order->id)->render();

        foreach ($this->expectedAppWording() as $expectedText) {
            $this->assertStringContainsString($expectedText, $html);
        }

        // Old, mismatched wording must be gone.
        $this->assertStringNotContainsString('مؤكد<', $html);
        $this->assertStringNotContainsString('جاهز للتسليم', $html);
        $this->assertStringNotContainsString('>الشحن<', $html);
        $this->assertStringNotContainsString('>مسلم<', $html);
    }

    public function test_order_edit_page_selects_the_orders_current_status_with_the_new_wording()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        $order = $this->createOrder('shipping');

        $html = (new OrderController())->edit($order->id)->render();

        $this->assertMatchesRegularExpression(
            '/<option value="shipping"[^>]*selected[^>]*>\s*في الطريق\s*</',
            $html
        );
    }

    /**
     * Filtered by this order's own unique order_number rather than relying
     * on default pagination — the shared dev database this test suite runs
     * against accumulates many real orders across the whole session, so an
     * unfiltered first page isn't guaranteed to include a just-created row.
     */
    public function test_partner_invoices_page_status_column_uses_the_apps_wording()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $confirmedOrder = $this->createOrder('confirmed');

        $view = (new PartnerFinanceController())->invoices(
            Request::create('/admin/partner-finance/invoices', 'GET', ['search' => $confirmedOrder->order_number])
        );
        $html = $view->render();

        $this->assertStringContainsString($confirmedOrder->order_number, $html);
        $this->assertStringContainsString('تم التأكيد', $html);
    }

    public function test_partner_invoices_page_status_filter_dropdown_uses_the_apps_wording()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new PartnerFinanceController())->invoices(Request::create('/admin/partner-finance/invoices', 'GET'));
        $html = $view->render();

        foreach ($this->expectedAppWording() as $status => $expectedText) {
            if ($status === 'pending') {
                continue;
            }
            $this->assertStringContainsString($expectedText, $html);
        }
    }
}
