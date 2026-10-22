<?php

namespace Tests\Feature\Api\Live;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\LiveVideoItemFavorite;
use App\Models\VideoFavorites;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FavoritesIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private function login(): User
    {
        $user = User::create([
            'name' => 'Favorites user', 'phone' => '01' . random_int(100000000, 999999999),
            'user_name' => 'favorite_' . bin2hex(random_bytes(6)),
            'password' => bcrypt('secret123'), 'user_type' => 'vendor', 'gender' => 'male',
        ]);
        Passport::actingAs($user, [], 'api');
        $this->withHeaders([
            'x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM',
            'Accept-Language' => 'en',
        ]);
        return $user;
    }

    private function favorite(string $entity, string $action, int $id): void
    {
        $prefix = $entity === 'animal' ? '/api/live/items/favorites/' : '/api/video/favorites/';
        $this->postJson($prefix . $action . '/' . $id)->assertOk()->assertJson(['success' => true]);
    }

    private function assertStatuses(LiveVideo $auction, array $items, bool $auctionFavorite, array $itemFavorites): void
    {
        foreach (['/api/live/video/', '/api/home/video/'] as $path) {
            $response = $this->getJson($path . $auction->id)->assertOk();
            $response->assertJsonPath('data.is_favorites', $auctionFavorite);
            $byId = collect($response->json('data.video_items'))->keyBy('id');
            foreach ($items as $index => $item) {
                $this->assertSame($itemFavorites[$index], $byId[$item->id]['is_favorites']);
            }
        }
    }

    public function test_animals_auctions_and_users_have_independent_favorites(): void
    {
        $firstUser = $this->login();
        $auction = LiveVideo::create(['user_id' => $firstUser->id, 'title' => 'Auction']);
        $otherAuction = LiveVideo::create(['user_id' => $firstUser->id, 'title' => 'Unrelated']);
        // Deliberately collide numeric IDs across the two entity types.
        $first = LiveVideoItem::forceCreate(['id' => $auction->id, 'live_video_id' => $auction->id, 'quantity' => 1]);
        $second = LiveVideoItem::create(['live_video_id' => $auction->id, 'quantity' => 1]);
        $this->favorite('auction', 'add', $otherAuction->id);
        $this->favorite('animal', 'add', $first->id);
        $this->favorite('animal', 'add', $first->id);
        $this->assertSame(1, LiveVideoItemFavorite::where('live_video_item_id', $first->id)->count());
        $this->assertStatuses($auction, [$first, $second], false, [true, false]);
        $this->favorite('auction', 'add', $auction->id);
        $this->favorite('animal', 'add', $second->id);
        $this->assertStatuses($auction, [$first, $second], true, [true, true]);
        $this->getJson('/api/live/items/favorites/list')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/video/favorites/list')->assertOk()->assertJsonCount(2, 'data');

        $secondUser = $this->login();
        $this->assertStatuses($auction, [$first, $second], false, [false, false]);
        $this->getJson('/api/live/items/favorites/list')->assertJsonCount(0, 'data');
        $this->getJson('/api/video/favorites/list')->assertJsonCount(0, 'data');
        $this->favorite('animal', 'add', $first->id);
        $this->favorite('auction', 'add', $auction->id);

        Passport::actingAs($firstUser, [], 'api');
        $this->favorite('animal', 'unfavorited', $first->id);
        $this->assertStatuses($auction, [$first, $second], true, [false, true]);
        $this->favorite('auction', 'unfavorited', $auction->id);
        $this->assertStatuses($auction, [$first, $second], false, [false, true]);
        $this->assertTrue($otherAuction->fresh()->isfavorite()->exists());
        $this->favorite('animal', 'add', $first->id);
        $this->assertStatuses($auction, [$first, $second], false, [true, true]);
        Passport::actingAs($secondUser, [], 'api');
        $this->assertStatuses($auction, [$first, $second], true, [true, false]);
    }

    public function test_legacy_auction_favorites_are_preserved_and_duplicates_removed_only_for_that_user(): void
    {
        $user = $this->login();
        $auction = LiveVideo::create(['title' => 'Legacy']);
        $legacy = VideoFavorites::create(['video_id' => $auction->id, 'user_id' => $user->id]);
        VideoFavorites::create(['video_id' => $auction->id, 'user_id' => $user->id]);
        $migration = require database_path('migrations/2026_10_22_010000_isolate_animal_favorites.php');
        $migration->up();
        $migration->down();
        $migration->up();
        $this->assertSame($auction->id, (int) $legacy->fresh()->video_id);
        $this->getJson('/api/video/favorites/list')->assertJsonPath('data.0.id', $auction->id);
        $this->favorite('auction', 'unfavorited', $auction->id);
        $this->assertFalse($auction->isfavorite()->exists());
        $this->assertNotNull($legacy->fresh()->deleted_at);
    }

    public function test_unknown_animals_do_not_create_favorites_and_likes_do_not_mark_auctions_favorite(): void
    {
        $user = $this->login();
        $auction = LiveVideo::create(['title' => 'Only liked']);
        $auction->likes()->create(['user_id' => $user->id]);
        $this->getJson('/api/home/video/' . $auction->id)->assertJsonPath('data.is_favorites', false);
        $this->postJson('/api/live/items/favorites/add/999999999')->assertJson(['success' => false]);
        $this->assertSame(0, LiveVideoItemFavorite::where('user_id', $user->id)->count());
    }

    public function test_transfer_preserves_original_favorites_without_copying_them_to_new_entities(): void
    {
        $user = $this->login();
        $source = LiveVideo::create(['user_id' => $user->id, 'title' => 'Source', 'status' => 'end']);
        $target = LiveVideo::create(['user_id' => $user->id, 'title' => 'Target']);
        $item = LiveVideoItem::create(['live_video_id' => $source->id, 'quantity' => 1]);
        $this->favorite('animal', 'add', $item->id);
        $this->favorite('auction', 'add', $source->id);
        $copy = app(\App\Services\AuctionItemTransferService::class)
            ->transferItems($source, $target, [$item->id])->first();
        $this->assertTrue($item->isfavorite()->exists());
        $this->assertTrue($source->isfavorite()->exists());
        $this->assertStatuses($target, [$copy], false, [false]);
        $this->assertSame(1, $source->video_items()->count());
    }
}
