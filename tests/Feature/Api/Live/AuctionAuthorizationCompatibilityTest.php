<?php

namespace Tests\Feature\Api\Live;

use App\Http\Controllers\FirebaseController;
use App\Models\{Admin, LiveVideo, LiveVideoItem, OrderItem, VideoComment, WalletTransaction};
use App\Models\User\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Compatibility tests, not concurrency proofs. Baseline mode requires HEAD class overlays. */
class AuctionAuthorizationCompatibilityTest extends TestCase
{
    private $owner, $buyer, $auction, $item, $bid;
    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('AUTH_COMPATIBILITY_TESTS') !== '1') { $this->markTestSkipped('Disposable audit suite.'); }
        $this->assertGreaterThanOrEqual(80400, PHP_VERSION_ID);
        $this->assertSame('testing', app()->environment());
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('mazadat_bid_test', DB::connection()->getDatabaseName());
        DB::beginTransaction();
        $this->transactionStarted = true;
        $this->owner = $this->user('vendor');
        $this->buyer = $this->user('buyer');
        $this->auction = LiveVideo::create(['user_id'=>$this->owner->id, 'status'=>'start',
            'start_price'=>1000, 'commission_amount'=>0, 'service_fee'=>0, 'commission_payer'=>'buyer']);
        DB::table('videos')->insertOrIgnore(['id'=>$this->auction->id, 'title'=>'Legacy FK fixture']);
        $this->item = LiveVideoItem::create(['live_video_id'=>$this->auction->id,
            'user_id'=>$this->owner->id, 'seller_id'=>$this->owner->id, 'status'=>'working', 'bidding'=>100]);
        $this->bid = VideoComment::create(['video_id'=>$this->auction->id,
            'live_video_item_id'=>$this->item->id, 'user_id'=>$this->buyer->id, 'comment'=>'1100']);
        // Exercise auth:api and dashboard authorization, without copying an unrelated static API key.
        $this->withoutMiddleware(['APISettings', \App\Http\Middleware\APISettings::class, \App\Http\Middleware\SetDashboardLocale::class]);
        $this->withHeaders(['Accept-Language'=>'en']);
        $this->app->instance(FirebaseController::class, \Mockery::mock(FirebaseController::class)->shouldIgnoreMissing());
    }

    protected function tearDown(): void
    {
        try { if ($this->transactionStarted) { DB::rollBack(); } }
        finally { parent::tearDown(); }
    }

    private function user(string $type): User
    {
        return User::create(['name'=>'Authorization test', 'user_name'=>'auth_'.bin2hex(random_bytes(8)),
            'phone'=>'01'.random_int(100000000,999999999), 'password'=>bcrypt('test'),
            'user_type'=>$type, 'gender'=>'male']);
    }

    public static function userTypes(): array
    {
        return ['Subscriber'=>['vendor'], 'Buyer'=>['buyer'], 'Seller'=>['seller'],
            'Subscriber and Buyer'=>['buyer_vendor']];
    }

    private function award()
    {
        return $this->postJson('/api/live/items/auction-award/'.$this->item->id, ['comment_id'=>$this->bid->id]);
    }

    /** @dataProvider userTypes */
    public function test_existing_api_user_types_can_bid(string $type): void
    {
        $actor = $this->user($type);
        Passport::actingAs($actor, [], 'api');
        $this->postJson('/api/video/auctions/add', ['live_video_item_id'=>$this->item->id, 'comment'=>1200])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, VideoComment::where('live_video_item_id',$this->item->id)->where('user_id',$actor->id)->count());
    }

    /** @dataProvider userTypes */
    public function test_award_permission_is_ownership_not_user_type(string $type): void
    {
        $this->owner->update(['user_type'=>$type]);
        Passport::actingAs($this->owner, [], 'api');
        $this->award()->assertOk()->assertJsonPath('success', true);
        $this->assertEquals($this->buyer->id, $this->item->fresh()->user_finished_id);
        $this->assertSame(1, OrderItem::where('live_video_item_id',$this->item->id)->count());
    }

    /** @dataProvider userTypes */
    public function test_non_owner_award_records_original_vs_current_difference(string $type): void
    {
        $actor = $this->user($type);
        $this->item->update(['user_id'=>$actor->id, 'seller_id'=>$actor->id]);
        Passport::actingAs($actor, [], 'api');
        $baseline = getenv('AUTH_COMPATIBILITY_BASELINE') === '1';
        $this->award()->assertStatus($baseline ? 200 : 403)->assertJsonPath('success', $baseline);
        $this->assertEquals($baseline ? $this->buyer->id : null, $this->item->fresh()->user_finished_id);
        $this->assertSame($baseline ? 1 : 0, OrderItem::where('live_video_item_id',$this->item->id)->count());
    }

    public function test_owner_selecting_lower_bid_records_business_rule_difference(): void
    {
        VideoComment::create(['video_id'=>$this->auction->id, 'live_video_item_id'=>$this->item->id,
            'user_id'=>$this->buyer->id, 'comment'=>'1200']);
        Passport::actingAs($this->owner, [], 'api');
        $baseline = getenv('AUTH_COMPATIBILITY_BASELINE') === '1';
        $this->award()->assertOk()->assertJsonPath('success', $baseline);
        $this->assertEquals($baseline ? 1100 : null, $this->item->fresh()->finished_price);
    }

    private function admin(string $type = 'admin'): Admin
    {
        $admin = Admin::create(['name'=>'Authorization test', 'email'=>bin2hex(random_bytes(8)).'@example.test',
            'password'=>bcrypt('test'), 'type'=>$type, 'user_id'=>$this->owner->id]);
        if ($type === 'admin') {
            $admin->assignRole(Role::findOrCreate('Super Admin', 'admin'));
            $this->assertTrue($admin->hasRole('Super Admin', 'admin'));
        }
        return $admin;
    }

    public function test_super_admin_session_does_not_authenticate_api_guard(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->award()->assertStatus(401);
        $this->assertNull($this->item->fresh()->user_finished_id);
    }

    public function test_linked_super_admin_user_has_no_implicit_api_override(): void
    {
        $admin = $this->admin();
        $this->auction->update(['user_id'=>$this->buyer->id, 'admin_id'=>$admin->id, 'partner_id'=>$admin->id]);
        Passport::actingAs($this->owner, [], 'api');
        $baseline = getenv('AUTH_COMPATIBILITY_BASELINE') === '1';
        $this->award()->assertStatus($baseline ? 200 : 403)->assertJsonPath('success', $baseline);
    }

    /** @dataProvider dashboardScopes */
    public function test_dashboard_settlement_scope_is_preserved(string $type, bool $owns, bool $allowed): void
    {
        $admin = $this->admin($type);
        $this->auction->update(['admin_id'=>$owns ? $admin->id : null]);
        $this->item->update(['finished_price'=>1100, 'user_finished_id'=>$this->buyer->id]);
        $order = OrderService::attachWonItem($this->item);
        $this->actingAs($admin, 'admin');
        $response = $this->put(route('admin.orders.update', $order->id, false), ['payment_status'=>'paid', 'status'=>'confirmed']);
        if ($allowed) {
            $response->assertRedirect();
            $this->assertSame('paid', $order->fresh()->payment_status);
            $this->assertNotNull($order->fresh()->settled_at);
            $this->assertSame(2, WalletTransaction::where('order_id',$order->id)->count());
        } else {
            $response->assertStatus(403);
            $this->assertSame('unpaid', $order->fresh()->payment_status);
            $this->assertSame(0, WalletTransaction::where('order_id',$order->id)->count());
        }
    }

    /** @dataProvider userTypes */
    public function test_fee_access_tracks_order_buyer_not_user_type(string $type): void
    {
        $this->buyer->update(['user_type'=>$type]);
        $this->item->update(['finished_price'=>1100, 'user_finished_id'=>$this->buyer->id]);
        $order = OrderService::attachWonItem($this->item);
        $payload = ['order_item_id'=>$order->items[0]->id, 'price'=>100,
            'custom_name'=>['en'=>'Fee', 'ar'=>'Fee']];
        Passport::actingAs($this->owner, [], 'api');
        $this->postJson('/api/user/piece-services', $payload)->assertStatus(403);
        Passport::actingAs($this->buyer, [], 'api');
        $this->postJson('/api/user/piece-services', $payload)->assertOk()->assertJsonPath('success', true);
        $this->assertEquals(100, $order->fresh()->piece_services_total);
        $order->update(['payment_status'=>'paid']);
        $this->postJson('/api/user/piece-services', $payload)->assertStatus(422);
    }

    public function test_new_lot_after_payment_records_business_rule_difference(): void
    {
        $this->item->update(['finished_price'=>1100, 'user_finished_id'=>$this->buyer->id]);
        $order = OrderService::attachWonItem($this->item);
        $order->update(['payment_status'=>'paid']);
        $second = LiveVideoItem::create(['live_video_id'=>$this->auction->id,
            'seller_id'=>$this->owner->id, 'finished_price'=>1200, 'user_finished_id'=>$this->buyer->id]);
        $baseline = getenv('AUTH_COMPATIBILITY_BASELINE') === '1';
        $rejected = false;
        try { OrderService::attachWonItem($second); }
        catch (\DomainException $e) {
            $rejected = true;
            $this->assertSame('Cannot add an award to a paid or settled order.', $e->getMessage());
        }
        $this->assertSame(! $baseline, $rejected);
        $this->assertSame($baseline ? 2 : 1, $order->items()->count());
        $this->assertEquals($baseline ? 2300 : 1100, $order->fresh()->subtotal);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }
    public static function dashboardScopes(): array
    {
        return ['Super Admin foreign auction'=>['admin',false,true],
            'Linked dashboard own auction'=>['partner',true,true],
            'Linked dashboard foreign auction'=>['partner',false,false]];
    }
}