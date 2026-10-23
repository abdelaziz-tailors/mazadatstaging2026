<?php

namespace Tests\Feature\Api\Live;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MobileExistingAuctionTransferTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'en',
        ];
    }

    private function createScenario(): array
    {
        $user = User::create([
            'name' => 'Transfer Organizer',
            'user_name' => 'transfer_' . bin2hex(random_bytes(8)),
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);

        $source = LiveVideo::create([
            'user_id' => $user->id,
            'title' => 'Ended source',
            'status' => 'end',
        ]);
        $target = LiveVideo::create([
            'user_id' => $user->id,
            'title' => 'Existing target',
            'status' => 'pending',
        ]);
        $item = LiveVideoItem::create([
            'live_video_id' => $source->id,
            'title' => 'Unsold source item',
            'quantity' => 1,
            'status' => 'pending',
        ]);
        $item->pieces()->create([
            'piece_number' => 1,
            'age' => 'adult',
            'weight' => 80,
            'identifier' => 'piece-1',
            'baham_count' => 1,
        ]);

        Passport::actingAs($user, [], 'api');

        return compact('user', 'source', 'target', 'item');
    }

    public function test_single_item_transfer_to_existing_auction_persists_and_returns_the_target(): void
    {
        $scenario = $this->createScenario();

        $response = $this->withHeaders($this->headers())->postJson('/api/live/transfer', [
            'item_id' => $scenario['item']->id,
            'target_auction_id' => $scenario['target']->id,
            'transfer_mode' => 'existing',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $response->assertJsonPath('data.target_auction.id', $scenario['target']->id);

        $copy = LiveVideoItem::where('transferred_from_item_id', $scenario['item']->id)->latest('id')->first();
        $this->assertNotNull($copy);
        $response->assertJsonPath('data.transferred_items.0.id', $copy->id);
        $this->assertSame((int) $scenario['target']->id, (int) $copy->live_video_id);
        $this->assertSame(1, $copy->pieces()->count());
    }

    public function test_items_transfer_alias_returns_the_same_persisted_result(): void
    {
        $scenario = $this->createScenario();

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/transfer', [
            'item_id' => $scenario['item']->id,
            'target_auction_id' => $scenario['target']->id,
            'transfer_mode' => 'existing',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertTrue($scenario['target']->video_items()
            ->where('transferred_from_item_id', $scenario['item']->id)
            ->exists());
    }

    public function test_target_auction_details_endpoint_contains_the_transferred_item(): void
    {
        $scenario = $this->createScenario();

        $this->withHeaders($this->headers())->postJson('/api/live/transfer', [
            'source_auction_id' => $scenario['source']->id,
            'item_ids' => [$scenario['item']->id],
            'target_auction_id' => $scenario['target']->id,
            'transfer_mode' => 'existing',
        ])->assertStatus(200);

        $details = $this->withHeaders($this->headers())
            ->getJson('/api/live/video/' . $scenario['target']->id);

        $details->assertStatus(200)->assertJson(['success' => true]);
        $this->assertContains(
            LiveVideoItem::where('transferred_from_item_id', $scenario['item']->id)->value('id'),
            collect($details->json('data.video_items'))->pluck('id')->all()
        );
    }

    public function test_duplicate_transfer_to_the_same_target_is_rejected(): void
    {
        $scenario = $this->createScenario();
        $payload = [
            'item_id' => $scenario['item']->id,
            'target_auction_id' => $scenario['target']->id,
            'transfer_mode' => 'existing',
        ];

        $this->withHeaders($this->headers())->postJson('/api/live/transfer', $payload)->assertStatus(200);
        $second = $this->withHeaders($this->headers())->postJson('/api/live/transfer', $payload);

        $second->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_current_listings_hide_transfer_ancestors_and_preserve_item_details(): void
    {
        $scenario = $this->createScenario();
        $source = $scenario['source'];
        $target = $scenario['target'];
        $original = $scenario['item'];
        $original->update(['title_ar' => 'Animal', 'information_ar' => 'Details', 'address' => 'Farm']);
        $unaffected = LiveVideoItem::create([
            'live_video_id' => $source->id, 'title' => 'Sold animal',
            'quantity' => 1, 'finished_price' => 125,
        ]);
        $service = app(\App\Services\AuctionItemTransferService::class);
        $copy = $service->transferItems($source, $target, [$original->id])->first();

        $this->assertCurrentAuctionItems($source, [$unaffected->id]);
        $this->assertCurrentAuctionItems($target, [$copy->id]);

        $details = $this->withHeaders($this->headers())->getJson('/api/live/video/' . $target->id);
        $details->assertJsonPath('data.video_items.0.title', 'Animal')
            ->assertJsonPath('data.video_items.0.information', 'Details')
            ->assertJsonPath('data.video_items.0.address', 'Farm')
            ->assertJsonPath('data.video_items.0.quantity', 1)
            ->assertJsonPath('data.video_items.0.pieces.0.identifier', 'piece-1');

        $target->update(['status' => 'end']);
        $third = LiveVideo::create([
            'user_id' => $scenario['user']->id, 'title' => 'Third auction', 'status' => 'pending',
        ]);
        $latest = $service->transferItems($target, $third, [$copy->id])->first();

        $this->assertCurrentAuctionItems($source, [$unaffected->id]);
        $this->assertCurrentAuctionItems($target, []);
        $this->assertCurrentAuctionItems($third, [$latest->id]);
        $this->assertSame($original->id, (int) $copy->fresh()->transferred_from_item_id);
        $this->assertSame($copy->id, (int) $latest->transferred_from_item_id);
        $this->assertSame(2, $source->video_items()->count());
        $this->assertSame(1, $target->video_items()->count());
        $this->assertSame('piece-1', $original->fresh()->pieces->first()->identifier);
        $this->assertSame('piece-1', $latest->pieces->first()->identifier);
        $historical = (new \App\Http\Resources\User\VideoItemResource($original->fresh()))
            ->resolve(request());
        $this->assertSame($original->id, $historical['id']);
        $this->assertSame('Details', $historical['information']);
        $this->assertSame(125.0, $source->fresh()->totalSales());
    }

    private function assertCurrentAuctionItems(LiveVideo $auction, array $expectedIds): void
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/live/video/' . $auction->id);
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals($expectedIds, collect($response->json('data.video_items'))->pluck('id')->all());

        $list = $this->withHeaders($this->headers())->getJson('/api/live/my-list');
        $list->assertOk();
        $entry = collect($list->json('data'))->firstWhere('id', $auction->id);
        $this->assertNotNull($entry);
        $this->assertEquals($expectedIds, collect($entry['video_items'])->pluck('id')->all());

        $home = $this->withHeaders($this->headers())->getJson('/api/home/video/' . $auction->id);
        $home->assertOk();
        $this->assertEquals($expectedIds, collect($home->json('data.video_items'))->pluck('id')->all());
        $search = $this->withHeaders($this->headers())->getJson('/api/home/auctions/search?q=' . urlencode($auction->title));
        $search->assertOk();
        $card = collect($search->json('data'))->firstWhere('id', $auction->id);
        $this->assertNotNull($card);
        $this->assertEquals($expectedIds, collect($card['video_items'])->pluck('id')->all());

        // The dashboard page loads this auction-specific DataTables action,
        // not VideoItemResource. Call directly, as other dashboard tests do.
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'draw' => 1, 'start' => 0, 'length' => 1,
        ]);
        $previousRequest = $this->app['request'];
        $this->app->instance('request', $request);
        // Yajra caches the request as a singleton; each direct call must
        // receive this request rather than an earlier auction's request.
        $this->app->forgetInstance('datatables.request');
        try {
            $table = app(\App\Http\Controllers\Dashboard\ProductController::class)
                ->get_data($auction->id, $request)->getData(true);
        } finally {
            $this->app->instance('request', $previousRequest);
            $this->app->forgetInstance('datatables.request');
        }
        $this->assertSame(count($expectedIds), $table['recordsTotal']);
        $this->assertSame(count($expectedIds), $table['recordsFiltered']);
        $this->assertEquals($expectedIds, collect($table['data'])->pluck('id')->all());
    }

    /** @dataProvider auctionTypes */
    public function test_destination_picker_filters_types_and_api_enforces_them(string $type, string $otherType): void
    {
        $scenario = $this->createScenario();
        $scenario['source']->update(['type' => $type]);
        $scenario['target']->update(['type' => $type]);
        $wrong = LiveVideo::create(['user_id' => $scenario['user']->id, 'title' => 'Wrong type', 'type' => $otherType]);
        $page = app(\App\Http\Controllers\Dashboard\ProductController::class)
            ->index(\Illuminate\Http\Request::create('/'), $scenario['source']->id);
        $this->assertContains($scenario['target']->id, $page->getData()['targetAuctions']->pluck('id')->all());
        $this->assertNotContains($wrong->id, $page->getData()['targetAuctions']->pluck('id')->all());
        $list = $this->withHeaders($this->headers())->getJson('/api/live/my-list?source_auction_id=' . $scenario['source']->id);
        $list->assertOk();
        $this->assertSame([$scenario['target']->id], collect($list->json('data'))->pluck('id')->all());
        $this->withHeaders($this->headers())->postJson('/api/live/transfer', [
            'item_id' => $scenario['item']->id, 'target_auction_id' => $wrong->id, 'transfer_mode' => 'existing',
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame(0, $scenario['item']->transferredCopies()->count());
        $response = $this->withHeaders($this->headers())->postJson('/api/live/transfer', [
            'item_id' => $scenario['item']->id, 'target_auction_id' => $scenario['target']->id, 'transfer_mode' => 'existing',
        ]);
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('data.target_auction.video_items'));
        $this->assertCurrentAuctionItems($scenario['source'], []);
        $this->assertCurrentAuctionItems($scenario['target'], [$response->json('data.transferred_items.0.id')]);
    }

    public static function auctionTypes(): array
    {
        return [['live', 'recorded'], ['recorded', 'live']];
    }

    /** @dataProvider auctionTypes */
    public function test_new_auction_api_rejects_type_changes_without_consuming_quota(string $type, string $otherType): void
    {
        $scenario = $this->createScenario();
        $scenario['source']->update(['type' => $type]);
        $subscription = \App\Models\UserSubscription::create([
            'user_id' => $scenario['user']->id, 'status' => 'approved',
            'remaining_auctions' => 2, 'expires_at' => now()->addMonth(),
        ]);
        $count = LiveVideo::count();
        $payload = [
            'item_id' => $scenario['item']->id, 'transfer_mode' => 'new',
            'new_auction' => [
                'type' => $otherType, 'title_ar' => 'New auction',
                'date_start_at' => now()->addDay()->toDateString(),
                'date_end_at' => now()->addDays(2)->toDateString(),
                'time_start_at' => '10:00', 'time_end_at' => '11:00', 'start_price' => 10,
            ],
        ];
        $this->withHeaders($this->headers())->postJson('/api/live/transfer', $payload)
            ->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame($count, LiveVideo::count());
        $this->assertSame(2, (int) $subscription->fresh()->remaining_auctions);
        $this->assertSame(0, $scenario['item']->transferredCopies()->count());

        if ($type === 'live') {
            $agora = \Mockery::mock(\App\Services\AgoraService::class);
            $agora->shouldReceive('generateChannelName')->once()->andReturn('test-transfer-channel');
            $agora->shouldReceive('generateToken')->once()->andReturn('test-transfer-token');
            $agora->shouldReceive('getAppId')->once()->andReturn('test-app-id');
            $this->app->instance(\App\Services\AgoraService::class, $agora);
        }
        $payload['new_auction']['type'] = $type;
        $response = $this->withHeaders($this->headers())->postJson('/api/live/transfer', $payload);
        $response->assertOk()->assertJson(['success' => true]);
        $response->assertJsonPath('data.target_auction.video_type', $type);
        $this->assertCount(1, $response->json('data.target_auction.video_items'));
        $this->assertSame(1, (int) $subscription->fresh()->remaining_auctions);
    }

    public function test_location_alias_that_is_not_an_owned_auction_is_rejected(): void
    {
        $scenario = $this->createScenario();

        $response = $this->withHeaders($this->headers())->postJson('/api/live/transfer', [
            'item_id' => $scenario['item']->id,
            'target_location_id' => 999999999,
            'transfer_mode' => 'existing',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('live_video_items', [
            'transferred_from_item_id' => $scenario['item']->id,
        ]);
    }
}
