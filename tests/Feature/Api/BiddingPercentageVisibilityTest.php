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
    private static $bearerKey;

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

    /** Issue real JWTs using ephemeral keys and Passport's own disposable tables. */
    private function prepareBearerAuthentication(): void
    {
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['passport.storage.database.connection' => config('database.default'),
            'passport.personal_access_client.id' => null, 'passport.personal_access_client.secret' => null]);
        foreach (['000002_create_oauth_access_tokens', '000004_create_oauth_clients',
            '000005_create_oauth_personal_access_clients'] as $migration) {
            (require base_path('vendor/laravel/passport/database/migrations/2016_06_01_'.$migration.'_table.php'))->up();
        }
        if (! self::$bearerKey) {
            // Keep key generation in memory; OpenSSL may write a .rnd file.
            \phpseclib3\Crypt\RSA::useInternalEngine();
            try {
                self::$bearerKey = \phpseclib3\Crypt\RSA::createKey(2048);
            } finally {
                \phpseclib3\Crypt\RSA::useBestEngine();
            }
        }
        $key = self::$bearerKey;
        config(['passport.private_key' => $key->toString('PKCS8'),
            'passport.public_key' => $key->getPublicKey()->toString('PKCS8')]);
        app(\Laravel\Passport\ClientRepository::class)->createPersonalAccessClient(null, 'Isolated visibility test', 'http://localhost');
        $this->withMiddleware([\App\Http\Middleware\APISettings::class, 'APISettings']);
        $this->withHeaders(['x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM',
            'Accept-Language' => 'en']);
    }

    /** @dataProvider homeBearerViewers */
    public function test_home_detail_real_passport_optional_authentication(string $payer, ?string $viewer, bool $visible): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario($payer);
        $credential = $viewer ? $s[$viewer]->createToken('Flutter home details') : null;
        $this->assertHomeBearerResponse($s, $credential?->accessToken, $viewer ? $s[$viewer]->id : null, $visible, $payer);
    }

    public static function homeBearerViewers(): array
    {
        return [
            'linked seller, seller payer' => ['seller', 'seller', true],
            'owner, seller payer' => ['seller', 'owner', true],
            'buyer, seller payer' => ['seller', 'buyer', false],
            'seller, buyer payer' => ['buyer', 'seller', true],
            'buyer, buyer payer' => ['buyer', 'buyer', true],
            'guest, seller payer' => ['seller', null, false],
        ];
    }

    /** @dataProvider homeInvalidBearers */
    public function test_home_detail_invalid_bearers_fall_back_to_public_guest(string $kind): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario('seller');
        $previous = Passport::$personalAccessTokensExpireIn;
        try {
            if ($kind === 'expired') {
                $expired = new \DateInterval('PT60S');
                $expired->invert = 1;
                Passport::$personalAccessTokensExpireIn = $expired;
            }
            $credential = $s['seller']->createToken('Invalid Flutter credential');
            if ($kind === 'revoked') {
                $credential->token->update(['revoked' => true]);
            } elseif ($kind === 'expired') {
                self::assertTrue($credential->token->expires_at->isPast());
            }
            $token = $kind === 'malformed' ? 'invalid-test-token' : $credential->accessToken;
            $this->assertHomeBearerResponse($s, $token, null, false, 'seller');
        } finally {
            Passport::$personalAccessTokensExpireIn = $previous;
        }
    }

    public static function homeInvalidBearers(): array
    {
        return ['malformed' => ['malformed'], 'revoked' => ['revoked'], 'expired' => ['expired']];
    }

    public function test_home_detail_forged_parameters_cannot_grant_bearer_or_guest_visibility(): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario('seller');
        $query = '?'.http_build_query(['uid' => $s['owner']->id, 'user_id' => $s['owner']->id,
            'seller_id' => $s['seller']->id, 'isHost' => true, 'user_type' => 'seller']);
        $credential = $s['buyer']->createToken('Forged Flutter parameters');
        $this->assertHomeBearerResponse($s, $credential->accessToken, $s['buyer']->id, false, 'seller', $query);
        $this->assertHomeBearerResponse($s, null, null, false, 'seller', $query);
    }

    private function assertHomeBearerResponse(array $s, ?string $token, ?int $expectedIdentity,
        bool $visible, string $payer, string $query = ''): void
    {
        $identities = [];
        app('events')->listen(\Illuminate\Foundation\Http\Events\RequestHandled::class,
            function ($event) use (&$identities) {
                $identities[] = $event->request->user('api')?->getAuthIdentifier();
            });
        // Discard guard state between HTTP requests, including bearer -> guest transitions.
        app('auth')->forgetGuards();
        $response = $this->getJson('/api/home/video/'.$s['auction']->id.$query,
            $token === null ? [] : ['Authorization' => 'Bearer '.$token])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('code', 200)
            ->assertJsonPath('message', 'Successfully')->assertJsonPath('data.id', $s['auction']->id);
        self::assertSame([$expectedIdentity], $identities);
        if ($expectedIdentity !== null) {
            self::assertSame($expectedIdentity, auth('api')->id());
            self::assertNotNull(auth('api')->user()->token());
        }
        self::assertSame(['success', 'code', 'message', 'data'], array_keys($response->json()));
        self::assertSame([
            'id', 'title', 'title_en', 'title_ar', 'status', 'start_price', 'image',
            'date_start_at', 'date_end_at', 'time_start_at', 'time_end_at',
            'schedule_start_utc', 'schedule_end_utc', 'schedule_timezone',
            'information', 'information_en', 'information_ar', 'terms_conditions',
            'terms_conditions_en', 'terms_conditions_ar', 'views_count', 'video_type',
            'partners_type', 'is_favorites', 'partner', 'city', 'video_items', 'user',
            'agora', 'isHost', 'auction_fees',
        ], array_keys($response->json('data')));
        self::assertSame(['app_id', 'channel_name', 'token', 'uid'], array_keys($response->json('data.agora')));
        self::assertIsArray($response->json('data.video_items'));
        self::assertIsBool($response->json('data.is_favorites'));
        $this->assertFees($response->json('data'), $visible, $payer);
    }

    /** @dataProvider viewers */
    public function test_real_passport_bearer_tokens_follow_the_detail_visibility_matrix(string $payer, string $viewer): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario($payer);
        // Verify Request::user('api') after the real middleware/controller/resource pipeline.
        $identity = null;
        app('events')->listen(\Illuminate\Foundation\Http\Events\RequestHandled::class,
            function ($event) use (&$identity) { $identity = $event->request->user('api')?->getAuthIdentifier(); });
        $credential = $s[$viewer]->createToken('Visibility regression');
        app('auth')->forgetGuards();
        $response = $this->getJson('/api/live/video/'.$s['auction']->id,
            ['Authorization' => 'Bearer '.$credential->accessToken])->assertOk()->assertJsonPath('success', true);
        self::assertSame($s[$viewer]->getAuthIdentifier(), $identity);
        self::assertSame($credential->token->id, auth('api')->user()->token()->id);
        $this->assertFees($response->json('data'), $payer === 'buyer' || in_array($viewer, ['owner', 'seller'], true), $payer);
    }

    public function test_real_bearer_authentication_rejects_missing_invalid_and_revoked_tokens(): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario('seller');
        $url = '/api/live/video/'.$s['auction']->id;
        $credential = $s['seller']->createToken('Revocation regression');
        foreach ([null, 'invalid-test-token'] as $token) {
            app('auth')->forgetGuards();
            $response = $this->getJson($url, $token ? ['Authorization' => 'Bearer '.$token] : [])->assertUnauthorized();
            self::assertArrayNotHasKey('data', $response->json());
        }
        $credential->token->update(['revoked' => true]);
        app('auth')->forgetGuards();
        $response = $this->getJson($url, ['Authorization' => 'Bearer '.$credential->accessToken])->assertUnauthorized();
        self::assertArrayNotHasKey('data', $response->json());
    }

    public function test_real_bearer_authentication_rejects_an_expired_signed_token(): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario('seller');
        $previous = Passport::$personalAccessTokensExpireIn;
        try {
            $expired = new \DateInterval('PT60S');
            $expired->invert = 1;
            Passport::$personalAccessTokensExpireIn = $expired;
            $credential = $s['seller']->createToken('Expiry regression');
            self::assertTrue($credential->token->expires_at->isPast());
            app('auth')->forgetGuards();
            $response = $this->getJson('/api/live/video/'.$s['auction']->id,
                ['Authorization' => 'Bearer '.$credential->accessToken])->assertUnauthorized();
            self::assertArrayNotHasKey('data', $response->json());
        } finally {
            Passport::$personalAccessTokensExpireIn = $previous;
        }
    }

    public function test_fee_migration_defaults_nullable_amounts_and_payer_constraints_on_disposable_schema(): void
    {
        $previous = config('database.default');
        config(['database.connections.fee_schema_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'database.default' => 'fee_schema_test']);
        try {
            self::assertSame(':memory:', DB::connection()->getDatabaseName());
            Schema::create('live_videos', function (Blueprint $t) { $t->id(); });
            (require database_path('migrations/2026_04_25_200000_add_fee_rates_to_live_videos.php'))->up();
            $columns = collect(DB::select('PRAGMA table_info(live_videos)'))->keyBy('name');
            self::assertSame('INTEGER', strtoupper($columns['commission_amount']->type));
            self::assertSame(0, $columns['commission_amount']->notnull);
            self::assertSame(0, $columns['service_fee']->notnull);
            self::assertSame(1, $columns['commission_payer']->notnull);
            self::assertSame("'buyer'", $columns['commission_payer']->dflt_value);
            DB::statement('INSERT INTO live_videos DEFAULT VALUES');
            $row = DB::table('live_videos')->first();
            self::assertNull($row->commission_amount);
            self::assertNull($row->service_fee);
            self::assertSame('buyer', $row->commission_payer);
            foreach ([null, 'invalid'] as $payer) {
                try {
                    DB::table('live_videos')->insert(['commission_payer' => $payer]);
                    self::fail('The migrated schema must reject an unknown payer.');
                } catch (\Illuminate\Database\QueryException $e) {
                    self::assertSame('23000', $e->errorInfo[0]);
                }
            }
        } finally {
            DB::disconnect('fee_schema_test');
            config(['database.default' => $previous]);
        }
    }

    public function test_real_bearer_identity_cannot_be_overridden_by_creator_or_forged_fields(): void
    {
        $this->prepareBearerAuthentication();
        $s = $this->scenario('seller');
        $s['item']->update(['user_id' => $s['unrelated']->id]);
        $other = $this->auction($s['owner']);
        LiveVideoItem::create(['live_video_id' => $other->id, 'seller_id' => $s['unrelated']->id]);
        $credential = $s['unrelated']->createToken('Identity regression');
        $query = http_build_query(['uid' => $s['owner']->id, 'user_id' => $s['owner']->id,
            'seller_id' => $s['seller']->id, 'isHost' => true, 'user_type' => 'seller']);
        app('auth')->forgetGuards();
        $response = $this->getJson('/api/live/video/'.$s['auction']->id.'?'.$query,
            ['Authorization' => 'Bearer '.$credential->accessToken])->assertOk();
        self::assertSame($s['unrelated']->id, request()->user('api')->getAuthIdentifier());
        $this->assertFees($response->json('data'), false, 'seller');

        $s['item']->update(['seller_id' => null]);
        $ownerCredential = $s['owner']->createToken('Owner relationship regression');
        app('auth')->forgetGuards();
        $response = $this->getJson('/api/live/video/'.$s['auction']->id,
            ['Authorization' => 'Bearer '.$ownerCredential->accessToken])->assertOk();
        $this->assertFees($response->json('data'), true, 'seller');
    }

    /** @dataProvider payers */
    public function test_real_creation_service_persists_fees_for_bearer_authenticated_owner(string $payer): void
    {
        $this->prepareBearerAuthentication();
        Schema::create('user_subscriptions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('status');
            $t->unsignedInteger('remaining_auctions'); $t->unsignedInteger('auctions_limit');
            $t->timestamp('expires_at'); $t->timestamp('started_at')->nullable();
            $t->timestamp('replaced_at')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        $owner = $this->user(['user_type' => 'vendor']);
        $this->admin($owner);
        $subscription = \App\Models\UserSubscription::create(['user_id' => $owner->id,
            'status' => 'approved', 'remaining_auctions' => 2, 'auctions_limit' => 2,
            'expires_at' => now()->addMonth()]);
        $credential = $owner->createToken('Creation regression');
        $headers = ['Authorization' => 'Bearer '.$credential->accessToken];
        $date = now()->addDays(2)->format('Y-m-d');
        app('auth')->forgetGuards();
        $response = $this->postJson('/api/live/add', ['title' => 'Persisted fees', 'title_ar' => 'Persisted fees',
            'information_ar' => 'Details', 'video_type' => 'photo', 'type' => 'photo',
            'date_start_at' => $date, 'date_end_at' => $date, 'time_start_at' => '10:00', 'time_end_at' => '11:00',
            'timezone' => 'Africa/Cairo', 'start_price' => 100,
            'commission_amount' => 5, 'commission_payer' => $payer, 'service_fee' => 20], $headers)
            ->assertOk()->assertJsonPath('success', true);
        $stored = LiveVideo::findOrFail($response->json('data.id'));
        self::assertEquals(5, $stored->commission_amount);
        self::assertSame($payer, $stored->commission_payer);
        self::assertEquals(20, $stored->service_fee);
        self::assertSame(1, $subscription->fresh()->remaining_auctions);
        $this->assertFees($response->json('data'), true, $payer);
        app('auth')->forgetGuards();
        $this->postJson('/api/live/update/'.$stored->id, ['title' => 'New title'], $headers)
            ->assertOk()->assertJsonPath('success', true);
        app('auth')->forgetGuards();
        $details = $this->getJson('/api/live/video/'.$stored->id, $headers)->assertOk()->json('data');
        $this->assertFees($details, true, $payer);
        self::assertSame('New title', $details['title_en']);
        self::assertSame(1, $subscription->fresh()->remaining_auctions);
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

    public function test_seller_access_follows_auction_relationship_even_with_a_buyer_account_type(): void
    {
        $s = $this->scenario('seller');
        $s['seller']->update(['user_type' => 'buyer']);
        Passport::actingAs($s['seller']->fresh(), [], 'api');
        foreach (['/api/home/video/', '/api/live/video/'] as $endpoint) {
            $this->assertFees($this->getJson($endpoint.$s['auction']->id)->assertOk()->json('data'), true, 'seller');
        }

        // An owner-owned lot has no explicit consignor; the subscriber still sees the fee.
        $s['item']->update(['seller_id' => null]);
        Passport::actingAs($s['owner'], [], 'api');
        $this->assertFees($this->getJson('/api/live/video/'.$s['auction']->id)->assertOk()->json('data'), true, 'seller');
        Passport::actingAs($s['seller']->fresh(), [], 'api');
        $this->assertFees($this->getJson('/api/live/video/'.$s['auction']->id)->assertOk()->json('data'), false, 'seller');
    }

    /** @dataProvider invalidPayers */
    public function test_unknown_payer_is_hidden_in_details_for_every_viewer($payer): void
    {
        $s = $this->scenario('seller');
        $s['auction']->update(['commission_payer' => $payer]);
        foreach (['owner', 'seller', 'buyer', 'unrelated'] as $viewer) {
            Passport::actingAs($s[$viewer], [], 'api');
            foreach (['/api/home/video/', '/api/live/video/'] as $endpoint) {
                $data = $this->getJson($endpoint.$s['auction']->id)->assertOk()->json('data');
                $this->assertNoPercentage($data);
                self::assertEquals(20, $data['auction_fees']['service_fee']);
                self::assertEquals($s['auction']->id, $data['id']);
            }
        }
    }

    public static function invalidPayers(): array
    {
        return ['null' => [null], 'unexpected' => ['invalid']];
    }

    public function test_owner_uid_and_null_host_flag_do_not_identify_the_viewer(): void
    {
        $s = $this->scenario('seller');
        $s['auction']->update(['service_fee' => null]);
        $url = '/api/home/video/'.$s['auction']->id;
        $guest = $this->getJson($url)->assertOk()->json('data');
        self::assertEquals($s['owner']->id, $guest['agora']['uid']);
        self::assertNull($guest['isHost']);
        self::assertSame(['commission_amount' => null, 'commission_payer' => null, 'service_fee' => null], $guest['auction_fees']);

        Passport::actingAs($s['seller'], [], 'api');
        $seller = $this->getJson($url)->assertOk()->json('data');
        self::assertSame($guest['agora']['uid'], $seller['agora']['uid']);
        self::assertNull($seller['isHost']);
        self::assertEquals(5, $seller['auction_fees']['commission_amount']);
        self::assertSame('seller', $seller['auction_fees']['commission_payer']);
        self::assertNull($seller['auction_fees']['service_fee']);

        // Missing optional amounts are distinct from hiding the configured payer.
        $s['auction']->update(['commission_amount' => null]);
        $seller = $this->getJson($url)->assertOk()->json('data');
        self::assertSame(['commission_amount' => null, 'commission_payer' => 'seller', 'service_fee' => null], $seller['auction_fees']);
    }

    /** @dataProvider payers */
    public function test_fee_updates_are_persisted_and_preserved_by_partial_updates(string $payer): void
    {
        $s = $this->scenario($payer === 'seller' ? 'buyer' : 'seller');
        $this->admin($s['owner']);
        $s['auction']->update(['status' => 'pending']);
        Passport::actingAs($s['owner'], [], 'api');
        $url = '/api/live/update/'.$s['auction']->id;
        $this->postJson($url, ['commission_amount' => 7, 'commission_payer' => $payer, 'service_fee' => 30])
            ->assertOk()->assertJsonPath('success', true);
        $this->postJson($url, ['title' => 'Updated auction'])
            ->assertOk()->assertJsonPath('success', true);
        $stored = $s['auction']->fresh();
        self::assertEquals(7, $stored->commission_amount);
        self::assertSame($payer, $stored->commission_payer);
        self::assertEquals(30, $stored->service_fee);

        foreach (['seller', 'buyer'] as $viewer) {
            Passport::actingAs($s[$viewer], [], 'api');
            $data = $this->getJson('/api/live/video/'.$stored->id)->assertOk()->json('data');
            $visible = $viewer === 'seller' || $payer === 'buyer';
            self::assertEquals($visible ? 7 : null, $data['auction_fees']['commission_amount']);
            self::assertSame($visible ? $payer : null, $data['auction_fees']['commission_payer']);
            self::assertEquals(30, $data['auction_fees']['service_fee']);
            self::assertSame('Updated auction', $data['title_en']);
            if (! $visible) { $this->assertNoPercentage($data); }
        }
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
