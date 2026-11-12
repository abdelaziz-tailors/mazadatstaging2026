<?php

namespace Tests\Feature\Bugfixes;

use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\FirebaseController;
use App\Models\LiveVideoItem;
use Illuminate\Support\ViewErrorBag;
use Laravel\Passport\Passport;
use Tests\Support\AuctionBugfixTestCase;

class AuctionSellerSelectionTest extends AuctionBugfixTestCase
{
    private function productPayload($auction): array
    {
        return ['video_id' => $auction->id, 'user_id' => $auction->user_id, 'title_ar' => 'Animal',
            'bidding' => '10', 'quantity' => '1', 'piece_multiplier_number' => '5',
            'pieces' => [['age' => '2 years', 'gender' => 'female', 'baham_count' => '0']]];
    }

    public function test_dashboard_form_uses_organizer_user_id_when_partner_id_is_a_legacy_admin_id(): void
    {
        $owner = $this->user(['user_type' => 'vendor', 'name' => 'Original organizer']);
        $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'partner_id' => 90001]);
        $view = app(ProductController::class)->create($auction->id);
        $html = view('dashboard.pages.products._form', $view->getData() + ['errors' => new ViewErrorBag()])->render();
        $dom = new \DOMDocument(); @$dom->loadHTML($html);
        $field = (new \DOMXPath($dom))->query('//input[@name="user_id"]')->item(0);
        $this->assertSame((string) $owner->id, $field->getAttribute('value'));
        $this->assertStringContainsString('Original organizer', $html);
    }

    public function test_dashboard_filters_approved_sellers_and_rejects_forged_selection(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $approved = $this->user(['name' => 'Approved seller']); $this->submission($approved, $owner, 'approved');
        $pending = $this->user(); $this->submission($pending, $owner);
        $foreign = $this->user(); $other = $this->user(['user_type' => 'vendor']);
        $this->submission($foreign, $other, 'approved');
        $deleted = $this->user(); $this->submission($deleted, $owner, 'approved')->delete();
        $this->assertSame([$approved->id], app(ProductController::class)->create($auction->id)->getData()['sellers']->pluck('id')->all());
        foreach ([$pending->id, $foreign->id, $deleted->id, $owner->id, null] as $invalid) {
            $this->postJson(route('admin.products.store'), $this->productPayload($auction) +
                ['seller_source' => 'from_list', 'seller_id' => $invalid])
                ->assertUnprocessable()->assertJsonValidationErrors('seller_id');
        }
        $this->assertSame(0, LiveVideoItem::count());
        $this->postJson(route('admin.products.store'), $this->productPayload($auction) +
            ['seller_source' => 'from_list', 'seller_id' => $approved->id])->assertRedirect();
        $this->assertSame($approved->id, LiveVideoItem::first()->seller_id);
        $this->assertSame('female', LiveVideoItem::first()->pieces->first()->gender);
        $this->assertSame([$approved->id], app(ProductController::class)->edit(LiveVideoItem::first()->id)->getData()['sellers']->pluck('id')->all());
    }

    public function test_single_owner_item_creation_handles_legacy_partner_id_and_invalid_inputs(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'partner_id' => 90001]);
        $payload = $this->productPayload($auction);
        $payload['user_id'] = 90001; // Old dashboard form sent an admin ID here.
        app(FirebaseController::class)->shouldReceive('create_item')->twice();
        $this->postJson(route('admin.products.store'), $payload)->assertRedirect();
        $this->assertSame($owner->id, LiveVideoItem::first()->user_id);
        $this->putJson(route('admin.products.update', LiveVideoItem::first()->id), $payload)->assertRedirect();
        $this->assertSame($owner->id, LiveVideoItem::first()->user_id);
        $this->postJson(route('admin.products.store'), array_replace($payload, ['bidding' => -1]))
            ->assertUnprocessable()->assertJsonValidationErrors('bidding');
        $this->postJson(route('admin.products.store'), array_replace($payload, ['video_id' => 99999]))
            ->assertUnprocessable()->assertJsonValidationErrors('video_id');
        $this->assertSame(1, LiveVideoItem::count());
    }

    public function test_other_partner_cannot_create_edit_or_update_auction_items(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        $this->admin($this->user(['user_type' => 'vendor']));
        $this->postJson(route('admin.products.store'), $this->productPayload($auction))->assertForbidden();
        $this->getJson(route('admin.products.edit', $item->id))->assertForbidden();
        $this->putJson(route('admin.products.update', $item->id), $this->productPayload($auction))->assertForbidden();
        Passport::actingAs($this->user(['user_type' => 'vendor']));
        $this->postJson('/api/live/items/add/'.$auction->id, [])->assertForbidden();
        $this->postJson('/api/live/items/update/'.$item->id, ['seller_id' => $owner->id])->assertForbidden();
    }

    public function test_owner_owned_animal_does_not_require_or_validate_a_separate_seller(): void
    {
        $owner = $this->user(['user_type' => 'vendor']);
        $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $payload = $this->productPayload($auction) + ['seller_source' => 'owner'];

        foreach ([[], ['seller_id' => null], ['seller_id' => $owner->id]] as $sellerInput) {
            $this->postJson(route('admin.products.store'), $payload + $sellerInput)->assertRedirect();
            $item = LiveVideoItem::latest('id')->first();
            $this->assertSame($owner->id, $item->user_id);
            $this->assertNull($item->seller_id);
            $this->putJson(route('admin.products.update', $item->id), $payload + $sellerInput)->assertRedirect();
            $this->assertNull($item->fresh()->seller_id);
        }
    }

    public function test_mobile_creation_rejects_a_seller_approved_for_another_partner(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $auction = $this->auction($owner);
        $seller = $this->user(); $this->submission($seller, $this->user(['user_type' => 'vendor']), 'approved');
        Passport::actingAs($owner);
        $this->postJson('/api/live/items/add/'.$auction->id, ['lineage_title_ar' => ['Animal'], 'seller_id' => [$seller->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('seller_id.0');
        $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        $this->postJson('/api/live/items/update/'.$item->id, ['seller_id' => $seller->id])
            ->assertUnprocessable()->assertJsonValidationErrors('seller_id');
        $this->assertNull($item->fresh()->seller_id);
    }
}
