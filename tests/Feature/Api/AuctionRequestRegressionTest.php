<?php

namespace Tests\Feature\Api;

use App\Http\Resources\User\SellerSubmissionResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerSubmission;
use App\Models\User\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuctionRequestRegressionTest extends TestCase
{
    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array', 'app.debug' => false]);
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');
        app()->setLocale('en');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'phone', 'email', 'password', 'user_type'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->string('image')->default('');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('cities', fn (Blueprint $table) => $table->id());
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();
            $table->softDeletes();
        });
        Schema::create('item_services', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
        });
        Schema::create('follow_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('follow_id');
        });
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('friend_id');
            $table->softDeletes();
        });
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('status')->default('end');
            $table->timestamps();
        });
        Schema::create('live_video_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_video_id')->constrained('live_videos')->cascadeOnDelete();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('user_finished_id')->nullable();
            $table->decimal('finished_price', 12, 2)->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
        foreach (['2026_06_22_100000_create_orders_table.php',
            '2026_06_22_100001_create_order_items_table.php',
            '2026_06_22_100002_create_wallet_transactions_table.php',
            '2026_04_13_120000_create_seller_submissions_tables.php',
            '2026_06_26_120000_create_order_item_services.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        require_once database_path('migrations/2026_06_18_130000_create_live_video_item_pieces_table.php');
        (new \CreateLiveVideoItemPiecesTable())->up();
    }

    private function user(string $type): User
    {
        return User::create(['name' => $type, 'phone' => '0500000000',
            'email' => $type.'@example.com', 'password' => bcrypt('correct-password'), 'user_type' => $type]);
    }

    public function test_sold_auction_deletion_preserves_buyer_and_seller_requests_and_accounting(): void
    {
        $owner = $this->user('vendor');
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $auction = LiveVideo::create(['user_id' => $owner->id, 'title_ar' => 'Auction']);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id,
            'seller_id' => $seller->id, 'user_finished_id' => $buyer->id, 'finished_price' => 500]);
        $order = Order::create(['live_video_id' => $auction->id, 'buyer_id' => $buyer->id,
            'payment_status' => 'paid', 'subtotal' => 500, 'total' => 500]);
        $line = OrderItem::create(['order_id' => $order->id, 'live_video_item_id' => $item->id,
            'seller_id' => $seller->id, 'finished_price' => 500]);
        $transaction = WalletTransaction::create(['user_id' => $seller->id, 'order_id' => $order->id,
            'order_item_id' => $line->id, 'live_video_item_id' => $item->id,
            'type' => 'seller_credit', 'amount' => 500, 'balance_after' => 500]);
        $transaction = $transaction->fresh();

        Passport::actingAs($buyer, [], 'api');
        $this->postJson('/api/live/delete/'.$auction->id)->assertJsonPath('success', false);
        Passport::actingAs($owner, [], 'api');
        $this->postJson('/api/live/delete/'.$auction->id)->assertStatus(422)->assertJsonPath('success', false);
        $this->postJson('/api/live/items/delete/'.$item->id)->assertJsonPath('success', false);
        $this->assertNotNull($auction->fresh());
        $this->assertSame($transaction->getAttributes(), $transaction->fresh()->getAttributes());
        $this->assertNotNull($line->fresh());
        $this->assertNotNull($order->fresh());

        Passport::actingAs($buyer, [], 'api');
        $this->getJson('/api/user/my-cart')->assertOk()->assertJsonPath('data.0.order_id', $order->id);
        $this->getJson('/api/user/auction/user-invoice')->assertOk()->assertJsonPath('data.0.id', $order->id);
        Passport::actingAs($seller, [], 'api');
        $this->getJson('/api/user/auction/seller-invoice-list')->assertOk()->assertJsonPath('data.0.order_id', $order->id);
    }

    public function test_legacy_awards_and_soft_deleted_orders_also_prevent_auction_deletion(): void
    {
        $owner = $this->user('vendor');
        $auction = LiveVideo::create(['user_id' => $owner->id]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'finished_price' => 0]);
        Passport::actingAs($owner, [], 'api');
        $this->postJson('/api/live/delete/'.$auction->id)->assertStatus(422);
        $item->update(['finished_price' => null]);
        $order = Order::create(['live_video_id' => $auction->id, 'buyer_id' => $owner->id]);
        $order->delete();
        $this->postJson('/api/live/delete/'.$auction->id)->assertStatus(422);
        $this->assertNotNull($auction->fresh());
    }

    public function test_buyer_invoice_list_tolerates_a_legacy_missing_auction(): void
    {
        $buyer = $this->user('buyer');
        $auction = LiveVideo::create([]);
        $order = Order::create(['live_video_id' => $auction->id, 'buyer_id' => $buyer->id]);
        Schema::disableForeignKeyConstraints();
        DB::table('live_videos')->where('id', $auction->id)->delete();
        Schema::enableForeignKeyConstraints();
        Passport::actingAs($buyer, [], 'api');
        $this->getJson('/api/user/auction/user-invoice')->assertOk()
            ->assertJsonPath('data.0.id', $order->id)->assertJsonPath('data.0.title', '');
    }

    public function test_buyer_and_seller_lists_tolerate_missing_auction_and_item(): void
    {
        $buyer = $this->user('buyer');
        $seller = $this->user('seller');
        $auction = LiveVideo::create([]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        $order = Order::create(['live_video_id' => $auction->id, 'buyer_id' => $buyer->id]);
        OrderItem::create(['order_id' => $order->id, 'live_video_item_id' => $item->id,
            'seller_id' => $seller->id, 'finished_price' => 500]);
        Schema::disableForeignKeyConstraints();
        DB::table('live_video_items')->where('id', $item->id)->delete();
        DB::table('live_videos')->where('id', $auction->id)->delete();
        Schema::enableForeignKeyConstraints();

        Passport::actingAs($buyer, [], 'api');
        $this->getJson('/api/user/my-cart')->assertOk()->assertJsonPath('data.0.order_id', $order->id)
            ->assertJsonPath('data.0.items_cart.0.can_add_services', false);
        $this->getJson('/api/user/auction/user-invoice')->assertOk()->assertJsonPath('data.0.id', $order->id);
        Passport::actingAs($seller, [], 'api');
        $this->getJson('/api/user/auction/seller-invoice-list')->assertOk()->assertJsonPath('data.0.order_id', $order->id);
    }

    /** @dataProvider expectedPrices */
    public function test_expected_price_survives_persistence_and_submission_details(float $price): void
    {
        $seller = $this->user('seller');
        $partner = $this->user('vendor');
        $submission = SellerSubmission::create(['user_id' => $seller->id, 'partner_id' => $partner->id,
            'sheep_type' => 'Sheep', 'age' => 'Adult', 'expected_price' => $price]);
        Passport::actingAs($seller, [], 'api');
        $response = $this->getJson('/api/user/seller-submissions/'.$submission->id)->assertOk();
        $this->assertEquals($price, $response->json('data.expected_price'));
        $this->assertEquals($price, (new SellerSubmissionResource($submission->fresh()->load('media', 'partner')))
            ->resolve()['expected_price']);

        Passport::actingAs($this->user('buyer'), [], 'api');
        $this->getJson('/api/user/seller-submissions/'.$submission->id)->assertStatus(404);
        Passport::actingAs($partner, [], 'api');
        $this->getJson('/api/user/seller-submissions/'.$submission->id)->assertStatus(404);
    }

    public static function expectedPrices(): array
    {
        return [[1234.50], [0.0]];
    }

    public function test_missing_expected_price_is_serialized_as_null(): void
    {
        $submission = new SellerSubmission(['expected_price' => null]);
        $submission->setRelation('media', collect())->setRelation('partner', null);
        $data = (new SellerSubmissionResource($submission))->resolve();
        $this->assertArrayHasKey('expected_price', $data);
        $this->assertNull($data['expected_price']);
    }

    public function test_only_the_auction_owner_can_delete_an_item(): void
    {
        $owner = $this->user('vendor');
        $auction = LiveVideo::create(['user_id' => $owner->id]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        Passport::actingAs($this->user('buyer'), [], 'api');
        $this->postJson('/api/live/items/delete/'.$item->id)->assertStatus(403)->assertJsonPath('success', false);
        $this->assertNotNull($item->fresh());
    }

    /** @dataProvider loginMethods */
    public function test_failed_login_uses_the_actual_method_and_locale(string $field, string $locale, string $key): void
    {
        $user = $this->user('buyer');
        $this->withMiddleware('APISettings');
        $this->withHeaders(['x-api-key' => self::API_KEY, 'Accept-Language' => $locale])
            ->postJson('/api/user/login', [$field => $user->{$field},
            'password' => 'incorrect', 'user_type' => 'buyer'])->assertOk()
            ->assertJsonPath('success', false)->assertJsonPath('code', 200)
            ->assertJsonPath('message', trans('app.'.$key, [], $locale));
    }

    public static function loginMethods(): array
    {
        return [['email', 'en', 'email_or_password_not_correct'],
            ['email', 'ar', 'email_or_password_not_correct'],
            ['phone', 'en', 'phone_or_password_not_correct'],
            ['phone', 'ar', 'phone_or_password_not_correct']];
    }
}
