<?php

namespace Tests\Feature\Api\Live;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\LiveVideoItemPiece;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Covers the new "manage a single piece" mobile endpoints — add/update/delete
 * one entry inside an item's `pieces` array without touching the others,
 * independent of the bulk `pieces` replace already done by
 * LiveVideoItemController::update() (which uses
 * LiveVideoItemPieceService::syncPieces()). Adding/deleting a piece keeps
 * the item's `quantity` in sync with the real piece count (see
 * LiveVideoItemPieceService::addPiece()/deletePiece()).
 *
 * Only the organizer who owns the auction (LiveVideo.user_id) the item
 * belongs to may call these — per explicit request, not just any
 * authenticated user.
 */
class LiveVideoItemPiecesTest extends TestCase
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

    private function createOrganizer(): User
    {
        return User::create([
            'name' => 'Organizer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
    }

    private function createItem(LiveVideo $liveVideo, array $overrides = []): LiveVideoItem
    {
        return LiveVideoItem::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'title' => 'Item',
            'quantity' => 0,
        ], $overrides));
    }

    private function createPiece(LiveVideoItem $item, array $overrides = []): LiveVideoItemPiece
    {
        return $item->pieces()->create(array_merge([
            'piece_number' => ($item->pieces()->max('piece_number') ?? 0) + 1,
            'age' => 'تام',
            'weight' => 80,
            'identifier' => 'ضاني 1',
            'baham_count' => 3,
        ], $overrides));
    }

    // ---------------------------------------------------------------- add

    public function test_add_piece_creates_a_piece_and_syncs_quantity_to_the_real_piece_count()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo, ['quantity' => 1]);
        $this->createPiece($item);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/add/' . $item->id, [
            'age' => 'سديس',
            'weight' => 90,
            'identifier' => 'ضاني 2',
            'baham_count' => '1',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(2, $item->pieces()->count());
        $this->assertEquals(2, $item->fresh()->quantity);

        $pieces = $response->json('data.pieces');
        $this->assertCount(2, $pieces);
        $newPiece = collect($pieces)->firstWhere('identifier', 'ضاني 2');
        $this->assertNotNull($newPiece);
        $this->assertEquals('سديس', $newPiece['age']);
        $this->assertEquals(90, $newPiece['weight']);
        $this->assertEquals(1, $newPiece['baham_count']);
    }

    public function test_add_piece_assigns_the_next_piece_number()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo);
        $this->createPiece($item, ['piece_number' => 1]);
        $this->createPiece($item, ['piece_number' => 2]);

        Passport::actingAs($organizer, [], 'api');

        $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/add/' . $item->id, [
            'identifier' => 'New piece',
        ]);

        // The pieces() relation already bakes in an ascending orderBy, so
        // sort the fetched collection instead of chaining another query
        // order (which wouldn't override the relation's own).
        $newest = $item->pieces()->get()->sortByDesc('piece_number')->first();
        $this->assertEquals(3, $newest->piece_number);
    }

    /**
     * These routes sit inside the "auth:api" middleware group (see
     * routes/api/user.php), so an unauthenticated request never reaches the
     * controller at all — it's rejected by the middleware itself, via the
     * app's exception handler formatting AuthenticationException as this
     * {success:false, code:401, ...} envelope.
     */
    public function test_add_piece_fails_without_authentication()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo);

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/add/' . $item->id, [
            'identifier' => 'x',
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_add_piece_returns_404_when_the_item_does_not_exist()
    {
        $organizer = $this->createOrganizer();
        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/add/999999', [
            'identifier' => 'x',
        ]);

        $response->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_add_piece_is_rejected_for_a_user_who_is_not_the_auctions_organizer()
    {
        $organizer = $this->createOrganizer();
        $intruder = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo);

        Passport::actingAs($intruder, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/add/' . $item->id, [
            'identifier' => 'x',
        ]);

        $response->assertStatus(403)->assertJson(['success' => false, 'code' => 403]);
        $this->assertEquals(0, $item->pieces()->count());
    }

    public function test_add_piece_rejects_a_non_numeric_weight()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/add/' . $item->id, [
            'weight' => 'not-a-number',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    // ------------------------------------------------------------- update

    public function test_update_piece_changes_only_the_submitted_fields()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo, ['quantity' => 1]);
        $piece = $this->createPiece($item, ['age' => 'تام', 'weight' => 80, 'identifier' => 'Original']);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/update/' . $piece->id, [
            'weight' => 95,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $piece->refresh();
        $this->assertEquals(95, $piece->weight);
        // Untouched fields keep their original values.
        $this->assertEquals('تام', $piece->age);
        $this->assertEquals('Original', $piece->identifier);
    }

    public function test_update_piece_does_not_change_the_items_quantity()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo, ['quantity' => 1]);
        $piece = $this->createPiece($item);

        Passport::actingAs($organizer, [], 'api');

        $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/update/' . $piece->id, [
            'identifier' => 'Renamed',
        ]);

        $this->assertEquals(1, $item->fresh()->quantity);
    }

    public function test_update_piece_returns_404_when_the_piece_does_not_exist()
    {
        $organizer = $this->createOrganizer();
        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/update/999999', [
            'identifier' => 'x',
        ]);

        $response->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_update_piece_is_rejected_for_a_user_who_is_not_the_auctions_organizer()
    {
        $organizer = $this->createOrganizer();
        $intruder = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo);
        $piece = $this->createPiece($item, ['identifier' => 'Original']);

        Passport::actingAs($intruder, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/update/' . $piece->id, [
            'identifier' => 'Hacked',
        ]);

        $response->assertStatus(403)->assertJson(['success' => false, 'code' => 403]);
        $this->assertEquals('Original', $piece->fresh()->identifier);
    }

    public function test_update_piece_rejects_a_non_numeric_weight()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo);
        $piece = $this->createPiece($item);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/update/' . $piece->id, [
            'weight' => 'not-a-number',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    // ------------------------------------------------------------- delete

    public function test_delete_piece_removes_it_and_syncs_quantity_to_the_real_piece_count()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo, ['quantity' => 2]);
        $pieceToDelete = $this->createPiece($item);
        $this->createPiece($item);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/delete/' . $pieceToDelete->id);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(1, $item->pieces()->count());
        $this->assertEquals(1, $item->fresh()->quantity);
        $this->assertNull(LiveVideoItemPiece::find($pieceToDelete->id));
    }

    public function test_delete_the_last_piece_drops_quantity_to_zero()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo, ['quantity' => 1]);
        $piece = $this->createPiece($item);

        Passport::actingAs($organizer, [], 'api');

        $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/delete/' . $piece->id);

        $this->assertEquals(0, $item->fresh()->quantity);
    }

    public function test_delete_piece_returns_404_when_the_piece_does_not_exist()
    {
        $organizer = $this->createOrganizer();
        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/delete/999999');

        $response->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_delete_piece_is_rejected_for_a_user_who_is_not_the_auctions_organizer()
    {
        $organizer = $this->createOrganizer();
        $intruder = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $organizer->id]);
        $item = $this->createItem($liveVideo, ['quantity' => 1]);
        $piece = $this->createPiece($item);

        Passport::actingAs($intruder, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/live/items/pieces/delete/' . $piece->id);

        $response->assertStatus(403)->assertJson(['success' => false, 'code' => 403]);
        $this->assertNotNull(LiveVideoItemPiece::find($piece->id));
    }
}
