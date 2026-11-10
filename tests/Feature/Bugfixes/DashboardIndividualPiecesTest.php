<?php

namespace Tests\Feature\Bugfixes;

use App\Models\LiveVideoItem;
use App\Services\DashboardProductSubmission;
use App\Services\LiveVideoItemPieceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\Support\AuctionBugfixTestCase;

class DashboardIndividualPiecesTest extends AuctionBugfixTestCase
{
    private $auction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $owner = $this->user(['user_type' => 'vendor']);
        $admin = $this->admin($owner);
        $this->auction = $this->auction($owner, ['admin_id' => $admin->id]);
        Schema::table('live_video_item_pieces', function (Blueprint $table) {
            $table->double('weight')->nullable();
            $table->string('piece_multiplier_number')->nullable();
            $table->unique(['live_video_item_id', 'piece_number']);
        });
        // Other isolated suites use a smaller schema in the same PHP process.
        $columns = new \ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns');
        $columns->setAccessible(true);
        $columns->setValue(null, []);
    }

    private function payload(array $extra = []): array
    {
        return $extra + ['video_id' => $this->auction->id, 'title_ar' => 'Animal', 'bidding' => 10,
            'quantity' => 7, 'individual_pieces' => 0];
    }

    private function item(): LiveVideoItem
    {
        return LiveVideoItem::create(['live_video_id' => $this->auction->id,
            'user_id' => $this->auction->user_id, 'title_ar' => 'Animal', 'quantity' => 7]);
    }

    public function test_off_creation_preserves_quantity_and_parent_fields_without_heads(): void
    {
        $this->postJson(route('admin.products.store'), $this->payload())->assertRedirect();
        $item = LiveVideoItem::firstOrFail();
        $this->assertSame(7, (int) $item->quantity);
        $this->assertSame('Animal', $item->title_ar);
        $this->assertSame($this->auction->user_id, $item->user_id);
        $this->assertCount(0, $item->pieces);
    }

    public function test_on_creation_accepts_multiple_heads_independently_of_quantity(): void
    {
        $this->postJson(route('admin.products.store'), $this->payload(['individual_pieces' => 1,
            'pieces' => [['id' => '', 'age' => '2', 'gender' => 'female'], ['id' => '', 'identifier' => 'Second', 'baham_count' => 0]],
        ]))->assertRedirect();
        $item = LiveVideoItem::firstOrFail();
        $this->assertSame(7, (int) $item->quantity);
        $this->assertSame([1, 2], $item->pieces->pluck('piece_number')->all());
        $this->assertSame('female', $item->pieces->first()->gender);
    }

    public function test_quantity_updates_and_off_updates_preserve_saved_heads_and_legacy_fields(): void
    {
        $item = $this->item();
        $piece = $item->pieces()->create(['piece_number' => 4, 'age' => 'Historical', 'weight' => 80, 'piece_multiplier_number' => '3']);
        foreach ([0, 1] as $enabled) {
            $this->putJson(route('admin.products.update', $item->id), $this->payload([
                'quantity' => 23, 'individual_pieces' => $enabled,
            ]))->assertRedirect();
            $this->assertSame(23, (int) $item->fresh()->quantity);
            $this->assertSame($piece->id, $item->fresh()->pieces->sole()->id);
            $this->assertSame(80.0, (float) $piece->fresh()->weight);
            $this->assertSame('3', $piece->fresh()->piece_multiplier_number);
        }
        // Even submitted hidden fields must not overwrite saved rows when OFF.
        $this->putJson(route('admin.products.update', $item->id), $this->payload([
            'pieces' => [['id' => $piece->id, 'age' => 'Changed']],
        ]))->assertRedirect();
        $this->assertSame('Historical', $piece->fresh()->age);
    }

    public function test_edit_add_and_explicit_delete_never_sync_quantity_or_replace_other_heads(): void
    {
        $item = $this->item();
        $first = $item->pieces()->create(['piece_number' => 1, 'age' => 'First', 'weight' => 70]);
        $second = $item->pieces()->create(['piece_number' => 2, 'identifier' => 'Keep']);
        $this->putJson(route('admin.products.update', $item->id), $this->payload([
            'individual_pieces' => 1,
            'pieces' => [['id' => $first->id, 'gender' => 'female'], ['age' => 'New']],
        ]))->assertRedirect();
        $this->assertSame(7, (int) $item->fresh()->quantity);
        $this->assertCount(3, $item->fresh()->pieces);
        $this->assertSame('First', $first->fresh()->age);
        $this->assertSame(70.0, (float) $first->fresh()->weight);
        $this->assertSame('female', $first->fresh()->gender);
        $this->assertSame('Keep', $second->fresh()->identifier);
        $this->putJson(route('admin.products.update', $item->id), $this->payload([
            'deleted_piece_ids' => [$second->id],
        ]))->assertRedirect();
        $this->assertCount(2, $item->fresh()->pieces);
        $this->assertSame(7, (int) $item->fresh()->quantity);
        $this->assertDatabaseMissing('live_video_item_pieces', ['id' => $second->id]);
    }

    public function test_invalid_fields_and_foreign_piece_ids_cannot_leave_partial_records(): void
    {
        $this->postJson(route('admin.products.store'), $this->payload(['individual_pieces' => 1,
            'pieces' => [['age' => 'Valid'], ['baham_count' => -1]],
        ]))->assertUnprocessable()->assertJsonValidationErrors('pieces.1.baham_count');
        $this->assertSame(0, LiveVideoItem::count());
        $other = $this->item();
        $foreign = $other->pieces()->create(['piece_number' => 1]);
        $this->postJson(route('admin.products.store'), $this->payload(['individual_pieces' => 1,
            'pieces' => [['id' => $foreign->id, 'age' => 'Hijack']],
        ]))->assertUnprocessable()->assertJsonValidationErrors('pieces');
        $this->assertSame(1, LiveVideoItem::count());
        $this->assertNull($foreign->fresh()->age);
        $item = $this->item();
        $this->putJson(route('admin.products.update', $item->id), $this->payload([
            'title_ar' => 'Must roll back', 'deleted_piece_ids' => [$foreign->id],
        ]))->assertUnprocessable();
        $this->assertSame('Animal', $item->fresh()->title_ar);
        $this->assertNotNull($foreign->fresh());
    }

    public function test_child_insert_failure_rolls_back_parent_and_earlier_children(): void
    {
        DB::unprepared("CREATE TRIGGER reject_bad_head BEFORE INSERT ON live_video_item_pieces WHEN NEW.identifier = 'fail' BEGIN SELECT RAISE(ABORT, 'fixture failure'); END");
        $this->postJson(route('admin.products.store'), $this->payload(['individual_pieces' => 1,
            'pieces' => [['age' => 'Valid'], ['identifier' => 'fail']],
        ]))->assertStatus(500);
        $this->assertSame(0, LiveVideoItem::count());
        $this->assertDatabaseCount('live_video_item_pieces', 0);
    }

    public function test_update_failure_rolls_back_explicit_deletion_and_parent_changes(): void
    {
        $item = $this->item();
        $piece = $item->pieces()->create(['piece_number' => 1, 'age' => 'Keep']);
        DB::unprepared("CREATE TRIGGER reject_bad_head BEFORE INSERT ON live_video_item_pieces WHEN NEW.identifier = 'fail' BEGIN SELECT RAISE(ABORT, 'fixture failure'); END");
        $this->putJson(route('admin.products.update', $item->id), $this->payload([
            'individual_pieces' => 1, 'title_ar' => 'Changed', 'quantity' => 99,
            'deleted_piece_ids' => [$piece->id], 'pieces' => [['identifier' => 'fail']],
        ]))->assertStatus(500);
        $this->assertSame('Animal', $item->fresh()->title_ar);
        $this->assertSame(7, (int) $item->fresh()->quantity);
        $this->assertSame('Keep', $piece->fresh()->age);
    }

    public function test_deleting_all_heads_explicitly_keeps_quantity_and_null_pieces_is_safe(): void
    {
        $item = $this->item();
        $piece = $item->pieces()->create(['piece_number' => 1, 'age' => 'Delete']);
        $this->putJson(route('admin.products.update', $item->id), $this->payload([
            'individual_pieces' => 1, 'pieces' => null, 'deleted_piece_ids' => [$piece->id],
        ]))->assertRedirect();
        $this->assertCount(0, $item->fresh()->pieces);
        $this->assertSame(7, (int) $item->fresh()->quantity);
    }

    public function test_piece_only_update_preserves_a_historical_zero_quantity_when_quantity_is_omitted(): void
    {
        $item = $this->item();
        $item->update(['quantity' => 0]);
        $payload = $this->payload(['individual_pieces' => 1, 'pieces' => [['age' => 'One']]]);
        unset($payload['quantity']);
        $this->putJson(route('admin.products.update', $item->id), $payload)->assertRedirect();
        $this->assertSame(0, (int) $item->fresh()->quantity);
        $this->assertCount(1, $item->fresh()->pieces);
    }

    public function test_signed_form_replays_do_not_duplicate_parent_or_new_children(): void
    {
        $payload = $this->payload(['individual_pieces' => 1, 'pieces' => [['age' => 'New']],
            'dashboard_submission' => DashboardProductSubmission::token('create:'.$this->auction->id)]);
        for ($i = 0; $i < 2; $i++) {
            $this->postJson(route('admin.products.store'), $payload)->assertRedirect();
        }
        $this->assertSame(1, LiveVideoItem::count());
        $item = LiveVideoItem::firstOrFail();
        $payload = $this->payload(['individual_pieces' => 1, 'pieces' => [['age' => 'Another']],
            'dashboard_submission' => DashboardProductSubmission::token('edit:'.$item->id)]);
        for ($i = 0; $i < 2; $i++) {
            $this->putJson(route('admin.products.update', $item->id), $payload)->assertRedirect();
        }
        $this->assertCount(2, $item->fresh()->pieces);
        $this->assertSame(7, (int) $item->fresh()->quantity);
    }

    public function test_foreign_auction_remains_forbidden_and_signed_scope_is_checked(): void
    {
        $this->postJson(route('admin.products.store'), $this->payload([
            'dashboard_submission' => DashboardProductSubmission::token('edit:1'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('dashboard_submission');
        $this->admin($this->user(['user_type' => 'vendor']));
        $this->postJson(route('admin.products.store'), $this->payload())->assertForbidden();
    }

    public function test_expired_form_cannot_be_resubmitted_after_replay_cache_lifetime(): void
    {
        $token = DashboardProductSubmission::token('create:'.$this->auction->id);
        \Carbon\Carbon::setTestNow(now()->addDay()->addMinute());
        $this->postJson(route('admin.products.store'), $this->payload(['dashboard_submission' => $token]))
            ->assertUnprocessable()->assertJsonValidationErrors('dashboard_submission');
        $this->assertSame(0, LiveVideoItem::count());
    }

    public function test_mobile_service_still_syncs_quantity_and_supports_partial_crud(): void
    {
        $item = $this->item();
        LiveVideoItemPieceService::syncPieces($item, [['age' => 'One'], ['age' => 'Two']]);
        $this->assertSame(2, (int) $item->fresh()->quantity);
        $piece = LiveVideoItemPieceService::addPiece($item, ['identifier' => 'Third']);
        $this->assertSame(3, (int) $item->fresh()->quantity);
        LiveVideoItemPieceService::updatePiece($piece, ['gender' => 'female']);
        $this->assertSame('Third', $piece->fresh()->identifier);
        LiveVideoItemPieceService::deletePiece($piece);
        $this->assertSame(2, (int) $item->fresh()->quantity);
        LiveVideoItemPieceService::syncPieces($item, []);
        $this->assertSame(2, (int) $item->fresh()->quantity);
    }

    public function test_mobile_piece_endpoints_retain_their_payload_shape_and_quantity_sync(): void
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->integer('friend_id');
            $table->string('type')->nullable(); $table->softDeletes();
        });
        \Laravel\Passport\Passport::actingAs($this->auction->user_Video, [], 'api');
        $item = $this->item();
        $item->update(['image' => '[]', 'video' => '']);
        $response = $this->postJson('/api/live/items/pieces/add/'.$item->id, [
            'age' => 'Two years', 'gender' => 'female', 'identifier' => 'Original', 'baham_count' => '2',
        ])->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, (int) $item->fresh()->quantity);
        $piece = $response->json('data.pieces.0');
        $this->assertSame(['id', 'piece_number', 'age', 'gender', 'identifier', 'baham_count'], array_keys($piece));
        $this->postJson('/api/live/items/pieces/update/'.$piece['id'], ['gender' => 'male'])
            ->assertOk()->assertJsonPath('data.pieces.0.identifier', 'Original')->assertJsonPath('data.pieces.0.gender', 'male');
        $this->assertSame(1, (int) $item->fresh()->quantity);
        $this->postJson('/api/live/items/pieces/add/'.$item->id, ['baham_count' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('baham_count');
        $this->postJson('/api/live/items/pieces/delete/'.$piece['id'])->assertOk()->assertJsonPath('data.pieces', []);
        $this->assertSame(0, (int) $item->fresh()->quantity);
    }

    public function test_shared_form_defaults_off_and_restores_ids_and_historical_age(): void
    {
        $controller = app(\App\Http\Controllers\Dashboard\ProductController::class);
        $view = $controller->create($this->auction->id);
        $html = view('dashboard.pages.products._form', $view->getData() + ['errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $this->assertStringContainsString('id="individual-pieces-toggle"', $html);
        $this->assertStringContainsString('hidden disabled', $html);
        $this->assertStringContainsString('dashboard_submission', $html);
        $this->assertStringNotContainsString('quantityInput', $html);
        $item = $this->item();
        $item->image = '[]'; $item->save();
        $piece = $item->pieces()->create(['piece_number' => 6, 'age' => 'Retired age']);
        $view = $controller->edit($item->id);
        $html = view('dashboard.pages.products._form', $view->getData() + ['errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $this->assertStringContainsString('Retired age', $html);
        $this->assertStringContainsString('"id":'.$piece->id, $html);
    }
}
