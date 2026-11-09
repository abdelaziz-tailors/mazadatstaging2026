<?php

namespace Tests\Unit;

use App\Http\Controllers\FirebaseController;
use App\Models\{LiveVideoItem, VideoComment, VideoCommentLike};
use App\Services\{AuctionAwardService, AuctionBidService, AuctionBidWithdrawalService, AuctionBidWithdrawalSync};
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Foundation\Application;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{DB, Facade};
use PHPUnit\Framework\TestCase;

/** Isolated SQLite only: never boots the project or reads .env. */
class AuctionBidWithdrawalTest extends TestCase
{
    private $app;
    private $database;
    private $firebase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->instance('config', new Repository());
        $this->database = new Manager($this->app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $this->app->instance('db', $this->database->getDatabaseManager());
        $this->app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, new class {
            public function json($data, $status = 200) { return new JsonResponse($data, $status); }
        });
        $this->app->instance('log', \Mockery::mock(\Psr\Log\LoggerInterface::class)->shouldIgnoreMissing());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
        $db = $this->database->getConnection();
        $db->setTransactionManager(new DatabaseTransactionsManager());
        self::assertSame('', $db->selectOne('PRAGMA database_list')->file);
        $db->getPdo()->sqliteCreateFunction('regexp', fn ($pattern, $value) => preg_match('/'.$pattern.'/', $value ?? ''));
        $db->statement('CREATE TABLE live_videos (id INTEGER PRIMARY KEY, user_id INTEGER, status TEXT, start_price TEXT, deleted_at TEXT)');
        $db->statement('CREATE TABLE live_video_items (id INTEGER PRIMARY KEY, live_video_id INTEGER, user_id INTEGER, seller_id INTEGER, status TEXT, bidding TEXT, finished_price TEXT, user_finished_id INTEGER, deleted_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE video_comments (id INTEGER PRIMARY KEY, video_id INTEGER, live_video_item_id INTEGER, comment_id INTEGER, user_id INTEGER, comment TEXT, deleted_at TEXT, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE video_comment_likes (id INTEGER PRIMARY KEY, comment_id INTEGER, user_id INTEGER, deleted_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE order_items (id INTEGER PRIMARY KEY, live_video_item_id INTEGER, order_id INTEGER, seller_id INTEGER, finished_price NUMERIC, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE orders (id INTEGER PRIMARY KEY, live_video_id INTEGER, buyer_id INTEGER, order_number TEXT, tax_percent NUMERIC, commission_percent NUMERIC, commission_payer TEXT, service_fee_per_item NUMERIC, payment_status TEXT, status TEXT, subtotal NUMERIC, tax_value NUMERIC, commission_value NUMERIC, service_fee_total NUMERIC, piece_services_total NUMERIC, total NUMERIC, settled_at TEXT, deleted_at TEXT, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE order_item_services (id INTEGER PRIMARY KEY, order_item_id INTEGER, price NUMERIC)');
        $db->statement('CREATE TABLE live_video_item_pieces (id INTEGER PRIMARY KEY, live_video_item_id INTEGER, piece_number INTEGER)');
        $db->statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, email_verified_at TEXT, password TEXT, remember_token TEXT, deleted_at TEXT, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE videos (id INTEGER PRIMARY KEY, video TEXT, deleted_at TEXT)');
        $db->table('videos')->insert(['id' => 10, 'video' => 'original/shared-video.mp4']);
        require_once database_path('migrations/2026_11_09_000000_create_auction_bid_withdrawals_table.php');
        $this->app->instance('db.schema', $db->getSchemaBuilder());
        (new \CreateAuctionBidWithdrawalsTable())->up();
        DB::table('live_videos')->insert(['id' => 10, 'user_id' => 9, 'status' => 'start', 'start_price' => '100']);
        DB::table('live_video_items')->insert(['id' => 20, 'live_video_id' => 10, 'status' => 'working', 'bidding' => '10']);
        VideoComment::forceCreate(['id' => 30, 'video_id' => 10, 'live_video_item_id' => 20, 'user_id' => 1, 'comment' => '110.00']);
        VideoComment::forceCreate(['id' => 31, 'video_id' => 10, 'live_video_item_id' => 20, 'user_id' => 2, 'comment' => '120.00']);
        $this->firebase = \Mockery::mock(FirebaseController::class);
        $this->app->instance(FirebaseController::class, $this->firebase);
    }

    protected function tearDown(): void
    {
        $this->database->getConnection()->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        \Mockery::close();
        parent::tearDown();
    }

    private function withdraw(int $id = 30, int $user = 9): void
    {
        (new AuctionBidWithdrawalService())->withdraw($id, $user);
    }

    private function httpDelete(string $id, ?int $user = 9, string $method = 'DELETE', array $body = [])
    {
        $this->app->register(\Illuminate\Events\EventServiceProvider::class);
        $this->app->register(\Illuminate\Routing\RoutingServiceProvider::class);
        $this->app->register(\Illuminate\Validation\ValidationServiceProvider::class);
        $translator = new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en');
        $this->app->instance('translator', $translator);
        $this->app->instance('laravellocalization', new class {
            public function getSupportedLocales() { return ['en' => []]; }
        });
        $guard = \Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('check')->andReturn($user !== null);
        $guard->shouldReceive('id')->andReturn($user);
        $guard->shouldReceive('user')->andReturn($user ? (object) ['id' => $user] : null);
        $auth = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('guard')->with('api')->andReturn($guard);
        $auth->shouldReceive('shouldUse')->with('api');
        $this->app->instance('auth', $auth);
        $this->app->instance(\Illuminate\Contracts\Auth\Factory::class, $auth);
        (new \Illuminate\Foundation\Providers\FormRequestServiceProvider($this->app))->boot();
        $router = $this->app['router'];
        $router->aliasMiddleware('auth', \App\Http\Middleware\Authenticate::class);
        $router->group(['prefix' => 'api', 'namespace' => 'App\Http\Controllers\api'], base_path('routes/api/user.php'));
        $request = \Illuminate\Http\Request::create('/api/video/auctions/'.$id, $method, $body);
        $this->app->instance('request', $request);
        return $router->dispatch($request);
    }

    public function test_real_delete_route_resolves_request_controller_and_owner(): void
    {
        $this->firebase->shouldReceive('removeAuctionBid')->once()->with(10, 20, 30);
        $response = $this->httpDelete('30');
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->getData(true)['success']);
        self::assertNull(VideoComment::find(30));
    }

    public function test_real_delete_route_requires_authentication(): void
    {
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->httpDelete('30', null);
    }

    /** @dataProvider deletionActors */
    public function test_delete_route_authorizes_only_the_related_auction_creator(string $actor, int $status): void
    {
        [$creator, $bidder, $otherBidder, $unrelated, $otherCreator] = $this->priceIntegrityUsers();
        DB::table('live_videos')->where('id', 10)->update(['user_id' => $creator->id]);
        DB::table('live_videos')->insert(['id' => 11, 'user_id' => $otherCreator->id, 'status' => 'start']);
        // Item creator and bid author intentionally differ from the auction creator.
        DB::table('live_video_items')->where('id', 20)->update(['user_id' => $bidder->id]);
        VideoComment::whereKey(30)->update(['user_id' => $bidder->id]);
        VideoComment::whereKey(31)->update(['user_id' => $otherBidder->id]);
        $reply = VideoComment::create(['video_id' => 10, 'comment_id' => 30, 'comment' => 'reply']);
        DB::table('video_comment_likes')->insert(['id' => 1, 'comment_id' => $reply->id]);
        if ($status === 200) {
            $this->firebase->shouldReceive('removeAuctionBid')->once()->with(10, 20, 30);
        } else {
            $this->firebase->shouldNotReceive('removeAuctionBid');
        }
        $actors = compact('creator', 'bidder', 'otherBidder', 'unrelated', 'otherCreator');
        $response = $this->httpDelete('30', $actors[$actor]->id);
        self::assertSame($status, $response->getStatusCode());
        self::assertSame($status === 200, $response->getData(true)['success']);
        if ($status === 200) {
            self::assertNull(VideoComment::find(30));
            self::assertNull(VideoComment::find($reply->id));
            self::assertTrue($reply->fresh()->trashed());
            self::assertSame(0, VideoCommentLike::count());
            self::assertNotNull(DB::table('auction_bid_withdrawals')->where('bid_id', 30)->value('synced_at'));
        } else {
            self::assertSame('Only the auction creator may delete this bid.', $response->getData(true)['message']);
            self::assertNotNull(VideoComment::find(30));
            self::assertNotNull($reply->fresh());
            self::assertSame(1, VideoCommentLike::count());
            self::assertSame(0, DB::table('auction_bid_withdrawals')->count());
        }
        self::assertNotNull(VideoComment::find(31));
        self::assertSame(2, DB::table('live_videos')->count());
        self::assertSame(1, DB::table('live_video_items')->count());
    }

    public function deletionActors(): array
    {
        return [
            'auction creator' => ['creator', 200],
            'bidder deleting own bid' => ['bidder', 403],
            'bidder deleting another bid' => ['otherBidder', 403],
            'unrelated authenticated user' => ['unrelated', 403],
            'another auction creator' => ['otherCreator', 403],
        ];
    }

    public function test_existing_post_route_still_places_a_bid(): void
    {
        $this->firebase->shouldReceive('AddAuctions')->once();
        $response = $this->httpDelete('add', 1, 'POST', ['live_video_item_id' => 20, 'comment' => '130']);
        self::assertTrue($response->getData(true)['success']);
        self::assertSame('130.00', VideoComment::orderByDesc('id')->first()->comment);
        self::assertSame(3, VideoComment::count());
    }

    public function test_real_delete_route_rejects_invalid_identifier(): void
    {
        self::assertFalse($this->httpDelete('invalid')->getData(true)['success']);
        self::assertSame(2, VideoComment::count());
    }

    public function test_withdrawal_soft_deletes_tree_and_likes_preserves_sources_and_price_and_allows_next_bid(): void
    {
        $reply = VideoComment::create(['video_id' => 10, 'comment_id' => 30, 'comment' => 'reply']);
        $nested = VideoComment::create(['video_id' => 10, 'comment_id' => $reply->id, 'comment' => 'nested']);
        DB::table('video_comment_likes')->insert([['id' => 1, 'comment_id' => 30], ['id' => 2, 'comment_id' => $nested->id], ['id' => 3, 'comment_id' => 31]]);
        $this->firebase->shouldReceive('removeAuctionBid')->once()->with(10, 20, 30);
        $this->firebase->shouldReceive('AddAuctions')->once();
        $this->withdraw();
        self::assertNull(VideoComment::find(30));
        self::assertNotNull(VideoComment::withTrashed()->find(30));
        self::assertNull(VideoComment::find($nested->id));
        self::assertSame(1, VideoCommentLike::count());
        self::assertSame('120.00', VideoComment::find(31)->comment);
        self::assertSame(1, DB::table('live_videos')->count());
        self::assertSame(1, DB::table('live_video_items')->count());
        self::assertSame(0, DB::table('order_items')->count());
        self::assertSame('original/shared-video.mp4', DB::table('videos')->where('id', 10)->value('video'));
        self::assertNull(DB::table('videos')->where('id', 10)->value('deleted_at'));
        self::assertNotNull(DB::table('auction_bid_withdrawals')->first()->synced_at);
        $placed = (new AuctionBidService())->place(20, 1, '130');
        self::assertSame('130.00', $placed->comment);
        self::assertNull(LiveVideoItem::without('pieces')->find(20)->user_finished_id);
    }

    /** @dataProvider rejectedWithdrawals */
    public function test_rejections_preserve_history(int $id, int $user, array $item, array $auction, int $status): void
    {
        if ($item) { DB::table('live_video_items')->update($item); }
        if ($auction) { DB::table('live_videos')->update($auction); }
        $this->firebase->shouldNotReceive('removeAuctionBid');
        try { $this->withdraw($id, $user); self::fail('Expected rejection'); }
        catch (HttpResponseException $e) { self::assertSame($status, $e->getResponse()->getStatusCode()); }
        self::assertSame(2, VideoComment::count());
        self::assertSame(0, DB::table('auction_bid_withdrawals')->count());
    }

    public function rejectedWithdrawals(): array
    {
        return [
            'highest' => [31, 9, [], [], 409], 'other bidder' => [30, 2, [], [], 403],
            'own bidder' => [30, 1, [], [], 403],
            'missing' => [999, 9, [], [], 404], 'guest' => [30, 0, [], [], 401],
            'closed auction' => [30, 9, [], ['status' => 'end'], 409],
            'closed item' => [30, 9, ['status' => 'finished'], [], 409],
            'awarded price' => [30, 9, ['finished_price' => '120'], [], 409],
            'awarded user' => [30, 9, ['user_finished_id' => 2], [], 409],
            'deleted item' => [30, 9, ['deleted_at' => '2026-01-01'], [], 409],
        ];
    }

    public function test_sync_failure_is_durable_and_retry_is_idempotent_and_repeated_delete_fails(): void
    {
        $this->firebase->shouldReceive('removeAuctionBid')->once()->andThrow(new \RuntimeException('offline'));
        $this->withdraw();
        self::assertNull(DB::table('auction_bid_withdrawals')->first()->synced_at);
        $retry = \Mockery::mock(FirebaseController::class);
        $retry->shouldReceive('removeAuctionBid')->once()->with(10, 20, 30);
        $this->app->instance(FirebaseController::class, $retry);
        (new AuctionBidWithdrawalSync())->sync(30);
        (new AuctionBidWithdrawalSync())->sync(30);
        self::assertNotNull(DB::table('auction_bid_withdrawals')->first()->synced_at);
        try { $this->withdraw(); self::fail('Expected missing bid'); }
        catch (HttpResponseException $e) { self::assertSame(404, $e->getResponse()->getStatusCode()); }
    }

    public function test_inconsistent_award_history_is_rejected(): void
    {
        VideoComment::create(['video_id' => 999, 'live_video_item_id' => 20, 'user_id' => 2, 'comment' => '140']);
        $this->expectException(HttpResponseException::class);
        $this->withdraw();
    }

    public function test_orders_and_equal_price_ties_are_rejected(): void
    {
        DB::table('order_items')->insert(['live_video_item_id' => 20]);
        try { $this->withdraw(); self::fail('Expected order rejection'); }
        catch (HttpResponseException $e) { self::assertSame(409, $e->getResponse()->getStatusCode()); }
        DB::table('order_items')->delete();
        VideoComment::whereKey(30)->update(['comment' => '120.00']);
        $this->expectException(HttpResponseException::class);
        $this->withdraw();
    }

    public function test_route_identifier_validation_cannot_be_overridden_by_body(): void
    {
        $factory = new \Illuminate\Validation\Factory(new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en'));
        foreach (['abc', '0', '-1', '1.1', '9223372036854775808'] as $id) {
            $request = \App\Http\Requests\api\Video\DeleteAuctionBidRequest::create('/'.$id, 'DELETE', ['bid_id' => 30]);
            $route = new \Illuminate\Routing\Route('DELETE', '{bid_id}', function () {});
            $route->bind($request);
            $request->setRouteResolver(fn () => $route);
            self::assertTrue($factory->make($request->validationData(), $request->rules())->fails());
        }
    }

    public function test_outer_rollback_preserves_bid_and_discards_firebase_removal(): void
    {
        $this->firebase->shouldNotReceive('removeAuctionBid');
        DB::beginTransaction();
        $this->withdraw();
        DB::rollBack();
        self::assertNotNull(VideoComment::find(30));
        self::assertSame(0, DB::table('auction_bid_withdrawals')->count());
    }

    public function test_firebase_adapter_removes_only_the_bid_path_and_never_republishes_a_deleted_bid(): void
    {
        $adapter = (new \ReflectionClass(FirebaseController::class))->newInstanceWithoutConstructor();
        $database = \Mockery::mock();
        $reference = \Mockery::mock();
        $database->shouldReceive('getReference')->once()->with('live/10/item/20/auctions/30')->andReturn($reference);
        $reference->shouldReceive('remove')->once();
        $property = new \ReflectionProperty(FirebaseController::class, 'database');
        $property->setAccessible(true);
        $property->setValue($adapter, $database);
        $adapter->removeAuctionBid(10, 20, 30);
        $record = VideoComment::find(30);
        $record->delete();
        // A delayed placement callback must not resurrect the withdrawn entry.
        $adapter->AddAuctions($record);
        self::assertNull(VideoComment::find(30));
    }

    public function test_failed_outbox_write_rolls_back_bid_replies_and_likes(): void
    {
        $reply = VideoComment::create(['video_id' => 10, 'comment_id' => 30, 'comment' => 'reply']);
        DB::table('video_comment_likes')->insert(['id' => 1, 'comment_id' => $reply->id]);
        DB::table('auction_bid_withdrawals')->insert([
            'bid_id' => 30, 'auction_id' => 10, 'item_id' => 20, 'created_at' => '2026-01-01',
        ]);
        $this->firebase->shouldNotReceive('removeAuctionBid');
        try { $this->withdraw(); self::fail('Expected outbox failure'); }
        catch (\Illuminate\Database\QueryException $e) {
            self::assertNotNull(VideoComment::find(30));
            self::assertNotNull($reply->fresh());
            self::assertSame(1, VideoCommentLike::count());
        }
    }

    private function priceIntegrityUsers(): array
    {
        $this->app->instance(\Faker\Generator::class, \Faker\Factory::create());
        // Reuse the existing factory with this project's nonstandard User namespace.
        $factory = new class extends \Database\Factories\UserFactory {
            protected $model = \App\Models\User\User::class;
        };
        return $factory->count(5)->create()->all();
    }

    private function submitAuditBid(int $userId, string $amount): VideoComment
    {
        $response = $this->httpDelete('add', $userId, 'POST', ['live_video_item_id' => 20, 'comment' => $amount]);
        self::assertTrue($response->getData(true)['success'], $response->getContent());
        return VideoComment::where('live_video_item_id', 20)->orderByDesc('id')->first();
    }

    private function assertAuditPrice(int $userId, string $attempt, string $current, string $minimum): void
    {
        $count = VideoComment::count();
        $response = $this->httpDelete('add', $userId, 'POST', ['live_video_item_id' => 20, 'comment' => $attempt]);
        self::assertFalse($response->getData(true)['success']);
        self::assertSame(['current_price' => $current, 'next_minimum' => $minimum], $response->getData(true)['data']);
        self::assertSame($count, VideoComment::count());
    }

    private function assertProvisionalWinner(VideoComment $bid, int $organizerId): void
    {
        // Execute the full award/order flow, then roll it back so bidding can continue.
        DB::beginTransaction();
        try {
            $winner = (new AuctionAwardService())->award(20, $bid->id, $organizerId);
            self::assertSame((int) $bid->user_id, (int) $winner->user_finished_id);
            self::assertSame($bid->comment, $winner->finished_price);
            self::assertSame((int) $bid->user_id, (int) DB::table('orders')->value('buyer_id'));
        } finally {
            DB::rollBack();
        }
    }

    /** @dataProvider withdrawalCounts */
    public function test_price_and_real_award_integrity_after_endpoint_withdrawal(bool $withdrawA): void
    {
        [$a, $b, $c, $d, $organizer] = $this->priceIntegrityUsers();
        // These are isolated test fixtures, not changes to a persistent database.
        DB::table('video_comments')->delete();
        DB::table('live_videos')->update(['user_id' => $organizer->id, 'start_price' => '900']);
        DB::table('live_video_items')->update(['user_id' => $organizer->id, 'bidding' => '100']);
        $this->firebase->shouldReceive('AddAuctions')->times(4);
        $bidA = $this->submitAuditBid($a->id, '1000');
        $bidB = $this->submitAuditBid($b->id, '1500');
        $bidC = $this->submitAuditBid($c->id, '2000');
        $this->assertProvisionalWinner($bidC, $organizer->id);
        $reply = VideoComment::create(['comment_id' => $bidB->id, 'video_id' => 10, 'user_id' => $a->id, 'comment' => 'reply']);
        $nested = VideoComment::create(['comment_id' => $reply->id, 'video_id' => 10, 'user_id' => $c->id, 'comment' => 'nested']);
        DB::table('video_comment_likes')->insert(['id' => 1, 'comment_id' => $nested->id]);
        $this->assertAuditPrice($d->id, '2000', '2000.00', '2100.00');

        $this->firebase->shouldReceive('removeAuctionBid')->once()->with(10, 20, $bidB->id);
        self::assertTrue($this->httpDelete((string) $bidB->id, $organizer->id)->getData(true)['success']);
        foreach ([$bidB, $reply, $nested] as $deleted) {
            self::assertNull(VideoComment::find($deleted->id));
            self::assertNotNull(VideoComment::withTrashed()->find($deleted->id)->deleted_at);
        }
        self::assertSame(0, VideoCommentLike::count());
        self::assertSame([$bidA->id, $bidC->id], VideoComment::where('live_video_item_id', 20)->whereNull('comment_id')->orderBy('id')->pluck('id')->all());
        self::assertSame(0, $bidB->replay_comment()->count());
        self::assertSame(0, DB::table('orders')->count());
        self::assertNull(LiveVideoItem::without('pieces')->find(20)->user_finished_id);

        // Prove the real award service selects C without permanently closing bidding.
        $this->assertProvisionalWinner($bidC, $organizer->id);

        if ($withdrawA) {
            $this->firebase->shouldReceive('removeAuctionBid')->once()->with(10, 20, $bidA->id);
            self::assertTrue($this->httpDelete((string) $bidA->id, $organizer->id)->getData(true)['success']);
            self::assertNull(VideoComment::find($bidA->id));
        }
        // Neither the withdrawn 1500 nor repeated withdrawals may lower this threshold.
        foreach (['1600', '2000', '2099.99'] as $attempt) {
            $this->assertAuditPrice($d->id, $attempt, '2000.00', '2100.00');
        }
        self::assertSame(409, $this->httpDelete((string) $bidC->id, $organizer->id)->getStatusCode());
        $bidD = $this->submitAuditBid($d->id, '2100');
        $this->assertAuditPrice($a->id, '2199.99', '2100.00', '2200.00');
        // The withdrawn bid must also be rejected as an explicit award candidate.
        try { (new AuctionAwardService())->award(20, $bidB->id, $organizer->id); self::fail('Deleted candidate accepted'); }
        catch (HttpResponseException $e) { self::assertFalse($e->getResponse()->getData(true)['success']); }
        $this->firebase->shouldReceive('UserAuctions')->once()->with(\Mockery::on(fn ($bid) => $bid->id === $bidD->id));
        $winnerD = (new AuctionAwardService())->award(20, null, $organizer->id);
        self::assertSame($d->id, (int) $winnerD->user_finished_id);
        self::assertSame('2100.00', $winnerD->finished_price);
        self::assertSame($d->id, (int) DB::table('orders')->value('buyer_id'));
        self::assertSame(2100.0, (float) DB::table('order_items')->value('finished_price'));
        self::assertSame(1, DB::table('orders')->count());
        self::assertSame(1, DB::table('order_items')->count());
        // Award retry preserves the winner and does not duplicate order lines/publication.
        (new AuctionAwardService())->award(20, $bidD->id, $organizer->id);
        self::assertSame(1, DB::table('order_items')->count());
        self::assertSame(409, $this->httpDelete((string) $bidC->id, $organizer->id)->getStatusCode());
        $this->assertAuditPrice($a->id, '2200', '2100.00', '2200.00');
    }

    public function withdrawalCounts(): array
    {
        return ['one withdrawal' => [false], 'multiple withdrawals' => [true]];
    }

    public function test_deleted_higher_historical_bid_has_zero_influence_on_placement_and_award(): void
    {
        [$a, $b, $c, $d, $organizer] = $this->priceIntegrityUsers();
        DB::table('live_videos')->update(['user_id' => $organizer->id]);
        // Model an already-archived historical row, not an allowed highest-bid withdrawal.
        $archived = VideoComment::create(['video_id' => 10, 'live_video_item_id' => 20,
            'user_id' => $b->id, 'comment' => '999999.00', 'deleted_at' => now()]);
        $this->firebase->shouldReceive('AddAuctions')->once();
        $this->assertAuditPrice($d->id, '129.99', '120.00', '130.00');
        $new = $this->submitAuditBid($d->id, '130');
        self::assertNull(VideoComment::find($archived->id));
        $this->firebase->shouldReceive('UserAuctions')->once()->with(\Mockery::on(fn ($bid) => $bid->id === $new->id));
        $winner = (new AuctionAwardService())->award(20, null, $organizer->id);
        self::assertSame($d->id, (int) $winner->user_finished_id);
        self::assertSame('130.00', $winner->finished_price);
    }
}

