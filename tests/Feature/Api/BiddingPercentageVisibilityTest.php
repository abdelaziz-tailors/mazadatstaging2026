<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Dashboard\VideoController;
use App\Http\Resources\User\{HomeVideoResource, MyLiveVideoResource, SingleLiveVideoResource};
use App\Models\{LiveVideo, LiveVideoItem, Order, VideoComment, WalletTransaction};
use App\Services\{AuctionAwardService, AuctionWalletSettlement, LifecycleNotificationService, OrderService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Http, Schema};
use Laravel\Passport\Passport;
use Tests\Support\AuctionBugfixTestCase;

/** Real routes and relations on disposable SQLite; no application database or external services. */
class BiddingPercentageVisibilityTest extends AuctionBugfixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('testing', app()->environment());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame('', DB::selectOne('PRAGMA database_list')->file);
        Http::fake();
        $this->app->instance(LifecycleNotificationService::class,
            \Mockery::mock(LifecycleNotificationService::class)->shouldIgnoreMissing());
        Schema::table('users', function (Blueprint $t) {
            $t->decimal('wallet_balance', 12, 2)->default(0);
        });
        Schema::table('live_video_items', function (Blueprint $t) {
            $t->timestamp('deleted_at')->nullable();
        });
        foreach (['live_video_likes' => ['live_video_id', 'user_id'],
            'video_views' => ['video_id', 'user_id'], 'video_favorites' => ['video_id', 'user_id'],
            'friends' => ['user_id', 'friend_id']] as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($columns) {
                $t->id();
                foreach ($columns as $column) { $t->unsignedBigInteger($column)->nullable(); }
                $t->string('type')->nullable(); $t->timestamps(); $t->softDeletes();
            });
        }
        Schema::create('order_item_services', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('order_item_id'); $t->decimal('price', 12, 2);
        });
        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->id();
            foreach (['user_id', 'order_id', 'order_item_id', 'live_video_item_id'] as $column) {
                $t->unsignedBigInteger($column)->nullable();
            }
            $t->string('type'); $t->decimal('amount', 12, 2); $t->decimal('balance_after', 12, 2);
            $t->string('description')->nullable(); $t->timestamps();
        });
        DB::connection()->getPdo()->sqliteCreateFunction('regexp',
            fn ($pattern, $value) => preg_match('/'.$pattern.'/', $value ?? ''));
    }

    private function scenario(string $payer): array
    {
        $owner = $this->user(['user_type' => 'vendor']);
        $seller = $this->user();
        $buyer = $this->user(['user_type' => 'buyer']);
        $unrelated = $this->user();
        $auction = $this->auction($owner, ['title' => 'Auctions', 'title_ar' => 'Auctions',
            'status' => 'start', 'commission_payer' => $payer, 'commission_amount' => 5, 'service_fee' => 20,
            'partner_id' => null, 'start_price' => 100]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'user_id' => $owner->id,
            'seller_id' => $seller->id, 'status' => 'working', 'title' => 'Lot', 'title_ar' => 'Lot',
            'bidding' => 10, 'quantity' => 1]);

        return compact('owner', 'seller', 'buyer', 'unrelated', 'auction', 'item');
    }

    private function assertFees(array $data, bool $visible, string $payer): void
    {
        self::assertSame(['commission_amount', 'commission_payer', 'service_fee'], array_keys($data['auction_fees']));
        self::assertEquals(20, $data['auction_fees']['service_fee']);
        if ($visible) {
            self::assertEquals(5, $data['auction_fees']['commission_amount']);
            self::assertSame($payer, $data['auction_fees']['commission_payer']);
        } else {
            self::assertNull($data['auction_fees']['commission_amount']);
            self::assertNull($data['auction_fees']['commission_payer']);
            $this->assertNoPercentage($data);
        }
    }

    private function assertNoPercentage(array $data): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['commission_amount', 'commission_percent', 'commission_payer'], true)) {
                self::assertNull($value, 'Restricted commission must not be exposed through '.$key);
            }
            if (is_array($value)) { $this->assertNoPercentage($value); }
        }
    }

    /** @dataProvider viewers */
    public function test_real_details_lists_and_nested_resources_follow_the_matrix(string $payer, string $viewer): void
    {
        $s = $this->scenario($payer);
        Passport::actingAs($s[$viewer], [], 'api');
        $id = $s['auction']->id;
        $visible = $payer === 'buyer' || in_array($viewer, ['seller', 'owner'], true);
        $before = DB::table('live_videos')->get()->toJson();
        foreach ([['/api/home/video/'.$id, false], ['/api/home/video/list', true],
            ['/api/home/user-videos/'.$s['owner']->id, true],
            ['/api/home/auctions/search?q=Auctions', true],
            ['/api/home/auctions/filter?status=inprogress', true],
            ['/api/live/video/'.$id, false]] as [$url, $collection]) {
            $response = $this->getJson($url)->assertOk()->assertJson(['success' => true]);
            $data = $response->json($collection ? 'data.0' : 'data');
            self::assertEquals($id, $data['id'], $url);
            $this->assertFees($data, $visible, $payer);
        }
        if ($viewer !== 'owner') {
            $response = $this->postJson('/api/live/start/'.$id)->assertOk()->assertJson(['success' => true]);
            $this->assertFees($response->json('data'), $visible, $payer);
        } else {
            $response = $this->getJson('/api/live/my-list')->assertOk()->assertJson(['success' => true]);
            $this->assertFees($response->json('data.0'), true, $payer);
        }
        // Mutation/transfer responses use these same serializers, including nested target_auction.
        foreach ([HomeVideoResource::class, MyLiveVideoResource::class, SingleLiveVideoResource::class] as $resource) {
            $response = response()->json(['target_auction' => new $resource($s['auction']->fresh())]);
            $this->assertFees($response->getData(true)['target_auction'], $visible, $payer);
        }
        self::assertSame($before, DB::table('live_videos')->get()->toJson());
        self::assertSame(0, Order::count());
        self::assertSame(0, WalletTransaction::count());
    }

    public static function viewers(): array
    {
        $cases = [];
        foreach (['seller', 'buyer'] as $payer) {
            foreach (['seller', 'buyer', 'unrelated', 'owner'] as $viewer) {
                $cases[$payer.' payer, '.$viewer] = [$payer, $viewer];
            }
        }
        return $cases;
    }

    public function test_guest_and_forged_viewer_fields_do_not_grant_visibility(): void
    {
        $s = $this->scenario('seller');
        $this->assertFees($this->getJson('/api/home/video/'.$s['auction']->id)->assertOk()->json('data'), false, 'seller');
        $this->getJson('/api/live/video/'.$s['auction']->id)->assertUnauthorized();
        Passport::actingAs($s['unrelated'], [], 'api');
        $query = http_build_query(['seller_id' => $s['seller']->id, 'user_id' => $s['owner']->id, 'user_type' => 'seller']);
        $this->assertFees($this->getJson('/api/live/video/'.$s['auction']->id.'?'.$query)->assertOk()->json('data'), false, 'seller');
        $html = $this->get('/share/auction/'.$s['auction']->id)->assertOk()->getContent();
        self::assertStringNotContainsString('commission_amount', $html);
        self::assertStringNotContainsString('commission_payer', $html);
    }

    public function test_membership_is_scoped_to_this_auction_and_not_cached_between_viewers(): void
    {
        $s = $this->scenario('seller');
        $other = $this->auction($s['owner'], ['commission_payer' => 'seller']);
        LiveVideoItem::create(['live_video_id' => $other->id, 'seller_id' => $s['unrelated']->id]);
        $url = '/api/home/video/'.$s['auction']->id;
        Passport::actingAs($s['seller'], [], 'api');
        $sellerResponse = $this->getJson($url)->assertOk()->json();
        $this->assertFees($sellerResponse['data'], true, 'seller');
        Passport::actingAs($s['unrelated'], [], 'api');
        $this->assertFees($this->getJson($url)->assertOk()->json('data'), false, 'seller');
        Passport::actingAs($s['buyer'], [], 'api');
        $buyerResponse = $this->getJson($url)->assertOk()->json();
        $this->assertFees($buyerResponse['data'], false, 'seller');
        // The complete envelope, nested keys and unrelated value types stay identical.
        $sellerResponse['data']['auction_fees']['commission_amount'] = null;
        $sellerResponse['data']['auction_fees']['commission_payer'] = null;
        self::assertSame($sellerResponse, $buyerResponse);
    }

    public function test_real_api_key_middleware_does_not_bypass_viewer_visibility(): void
    {
        $s = $this->scenario('seller');
        $this->withMiddleware([\App\Http\Middleware\APISettings::class, 'APISettings']);
        Passport::actingAs($s['buyer'], [], 'api');
        $url = '/api/live/video/'.$s['auction']->id;
        $this->getJson($url)->assertJson(['success' => false, 'code' => 403]);
        $headers = ['x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM',
            'Accept-Language' => 'en'];
        $this->assertFees($this->getJson($url, $headers)->assertOk()->json('data'), false, 'seller');
        Passport::actingAs($s['seller'], [], 'api');
        $this->assertFees($this->getJson($url, $headers)->assertOk()->json('data'), true, 'seller');
    }

    /** @dataProvider pdfViewers */
    public function test_buyer_pdf_hides_only_restricted_derived_commission(string $payer, string $viewer): void
    {
        $s = $this->scenario($payer);
        $order = $this->award($s);
        // A user can buy in an auction while also being its consignor or subscriber.
        DB::table('orders')->where('id', $order->id)->update(['buyer_id' => $s[$viewer]->id]);
        Passport::actingAs($s[$viewer], [], 'api');
        $order = $order->fresh();
        $before = $order->getRawOriginal();
        $visible = $payer === 'buyer' || in_array($viewer, ['seller', 'owner'], true);
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        Pdf::shouldReceive('loadView')->once()->withArgs(function ($view, $data) use ($visible, $order) {
            self::assertSame('pdf.invoice', $view);
            self::assertSame($visible, array_key_exists('sellerCommission', $data));
            if ($visible) { self::assertSame(50.0, $data['sellerCommission']); }
            self::assertSame(1000.0, $data['bidValue']);
            self::assertSame($order->commission_value, $data['buyerCommission']);
            self::assertSame(20.0, $data['sponsorshipFee']);
            self::assertSame(1000.0, $data['total']);
            $html = view($view, $data)->render();
            self::assertSame($visible, str_contains($html, 'عمولة البائع'));
            self::assertStringContainsString('عمولة المشتري', $html);
            self::assertStringContainsString('1,000.00', $html);
            if (! $visible) { self::assertStringNotContainsString('50.00', $html); }
            return true;
        })->andReturn($pdf);
        $pdf->shouldReceive('download')->once()->andReturn(response('%PDF-test', 200, ['Content-Type' => 'application/pdf']));
        $this->get('/api/user/auction/user-invoice/'.$order->id.'/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        self::assertSame($before, $order->fresh()->getRawOriginal());
        self::assertSame(0, WalletTransaction::count());
    }

    public static function pdfViewers(): array
    {
        return [['seller', 'buyer'], ['buyer', 'buyer'], ['seller', 'seller'], ['seller', 'owner']];
    }

    /** @dataProvider payers */
    public function test_native_pdf_renderer_downloads_the_filtered_invoice(string $payer): void
    {
        config(['dompdf.options.enable_remote' => false]);
        $s = $this->scenario($payer);
        $order = $this->award($s);
        Passport::actingAs($s['buyer'], [], 'api');
        $seen = false;
        view()->composer('pdf.invoice', function ($view) use ($payer, &$seen) {
            $seen = true;
            self::assertSame($payer === 'buyer', array_key_exists('sellerCommission', $view->getData()));
        });
        $response = $this->get('/api/user/auction/user-invoice/'.$order->id.'/pdf')
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        self::assertTrue($seen);
        self::assertStringStartsWith('%PDF-', $response->getContent());
        self::assertGreaterThan(1000, strlen($response->getContent()));
    }

    /** @dataProvider payers */
    public function test_cart_and_seller_invoice_keep_their_authorized_financial_breakdowns(string $payer): void
    {
        $s = $this->scenario($payer);
        $order = $this->award($s);
        Passport::actingAs($s['buyer'], [], 'api');
        $cart = $this->getJson('/api/user/my-cart')->assertOk()->json('data.0');
        self::assertEquals(1000, $cart['total_price']);
        self::assertEquals($order->commission_value, $cart['commission']);
        if ($payer === 'buyer') {
            self::assertSame('5%', $cart['commission_amount']);
        } else {
            $this->assertNoPercentage($cart);
        }
        Passport::actingAs($s['seller'], [], 'api');
        $invoice = $this->getJson('/api/user/auction/seller-invoice-list')->assertOk()->json('data.0');
        self::assertEquals($payer === 'seller' ? 50 : 0, $invoice['totals']['commission']);
        self::assertEquals($payer === 'seller' ? 930 : 980, $invoice['totals']['net']);
        self::assertNotEmpty($invoice['pdf_url']);
    }

    public function test_pdf_download_still_rejects_another_buyers_order(): void
    {
        $s = $this->scenario('seller');
        $order = $this->award($s);
        Passport::actingAs($s['unrelated'], [], 'api');
        Pdf::shouldReceive('loadView')->never();
        $this->getJson('/api/user/auction/user-invoice/'.$order->id.'/pdf')->assertOk()->assertJson(['success' => false]);
    }

    private function award(array $s): Order
    {
        $bid = VideoComment::create(['video_id' => $s['auction']->id, 'live_video_item_id' => $s['item']->id,
            'user_id' => $s['buyer']->id, 'comment' => '1000']);
        app(AuctionAwardService::class)->award($s['item']->id, $bid->id, $s['owner']->id);
        return Order::where('live_video_id', $s['auction']->id)->firstOrFail();
    }

    /** @dataProvider payers */
    public function test_award_snapshots_calculations_and_idempotent_settlement_are_preserved(string $payer): void
    {
        $s = $this->scenario($payer);
        // Explicit admin mapping avoids legacy partner ID ambiguity in this fixture.
        $admin = $this->admin($s['owner']);
        DB::table('live_videos')->where('id', $s['auction']->id)->update(['partner_id' => $admin->id]);
        $order = $this->award($s);
        self::assertSame(5.0, $order->commission_percent);
        self::assertSame($payer, $order->commission_payer);
        self::assertSame($payer === 'buyer' ? 1050.0 : 1000.0, $order->total);
        self::assertEquals($s['seller']->id, $order->items->first()->seller_id);
        self::assertEquals($s['buyer']->id, $s['item']->fresh()->user_finished_id);
        Passport::actingAs($s['buyer'], [], 'api');
        $before = $order->getRawOriginal();
        $this->getJson('/api/live/video/'.$s['auction']->id)->assertOk();
        self::assertSame($before, $order->fresh()->getRawOriginal());
        OrderService::updateOrderStatus($order, 'paid', 'confirmed');
        AuctionWalletSettlement::settleOrderIfNeeded($order->fresh());
        $sellerNet = $payer === 'seller' ? 930.0 : 980.0;
        self::assertEquals($sellerNet, $s['seller']->fresh()->wallet_balance);
        self::assertEquals(70.0, $s['owner']->fresh()->wallet_balance);
        self::assertEquals(0, $s['buyer']->fresh()->wallet_balance);
        self::assertSame(-1000.0, WalletTransaction::where('type', 'buyer_debit')->firstOrFail()->amount);
        self::assertSame($sellerNet, WalletTransaction::where('type', 'seller_credit')->firstOrFail()->amount);
        self::assertSame(70.0, WalletTransaction::where('type', 'partner_credit')->firstOrFail()->amount);
        $ledger = WalletTransaction::orderBy('id')->get()->toJson();
        AuctionWalletSettlement::settleOrderIfNeeded($order->fresh());
        self::assertSame($ledger, WalletTransaction::orderBy('id')->get()->toJson());
    }

    public static function payers(): array { return [['seller'], ['buyer']]; }

    /** @dataProvider payers */
    public function test_dashboard_admin_and_subscriber_keep_the_configured_percentage(string $payer): void
    {
        $s = $this->scenario($payer);
        $admin = $this->admin($s['owner']);
        DB::table('live_videos')->where('id', $s['auction']->id)->update(['admin_id' => $admin->id]);
        $view = app(VideoController::class)->show($s['auction']->id);
        self::assertEquals(5, $view->getData()['video']->commission_amount);
        self::assertSame($payer, $view->getData()['video']->commission_payer);
        $this->admin($s['owner'], 'super_admin');
        $view = app(VideoController::class)->show($s['auction']->id);
        self::assertEquals(5, $view->getData()['video']->commission_amount);
        Passport::actingAs($s['owner'], [], 'api');
        $this->assertFees($this->getJson('/api/live/my-list')->assertOk()->json('data.0'), true, $payer);
    }
}
