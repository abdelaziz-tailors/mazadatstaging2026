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
