<?php

namespace Tests\Feature\Services;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Services\AuctionItemTransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class AuctionItemTransferServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function subscribedOwnerId(): int
    {
        $user = \App\Models\User\User::create([
            'name' => 'Transfer subscriber', 'user_name' => 'subscription_'.bin2hex(random_bytes(8)),
            'phone' => '01'.random_int(100000000, 999999999),
            'password' => bcrypt('test'), 'user_type' => 'vendor', 'gender' => 'male',
        ]);
        \App\Models\UserSubscription::create([
            'user_id' => $user->id, 'subscription_type' => 'monthly', 'status' => 'approved',
            'auctions_limit' => 2, 'remaining_auctions' => 2, 'expires_at' => now()->addMonth(),
        ]);
        return $user->id;
    }

    public function test_transfer_to_existing_auction_persists_the_destination_relation(): void
    {
        $source = LiveVideo::create(['title' => 'Source', 'status' => 'end', 'type' => 'recorded']);
        $target = LiveVideo::create(['title' => 'Target', 'status' => 'pending', 'type' => 'recorded']);
        $item = LiveVideoItem::create([
            'live_video_id' => $source->id,
            'title' => 'Unsold item',
            'quantity' => 1,
            'status' => 'pending',
        ]);

        $created = app(AuctionItemTransferService::class)->transferItems($source, $target, [$item->id]);
        $copy = $created->first();

        $this->assertNotNull($copy);
        $this->assertSame($target->id, (int) $copy->live_video_id);
        $this->assertTrue($target->video_items()->whereKey($copy->id)->exists());
        $this->assertSame($source->id, (int) $item->fresh()->live_video_id);
    }

    public function test_create_new_live_auction_initializes_existing_agora_credentials(): void
    {
        $agora = Mockery::mock(\App\Services\AgoraService::class);
        $agora->shouldReceive('generateChannelName')->once()->andReturn('auction_test_channel');
        $agora->shouldReceive('generateToken')->once()->with('auction_test_channel')->andReturn('valid-test-token');
        $agora->shouldReceive('getAppId')->once()->andReturn('valid-test-app-id');
        $this->app->instance(\App\Services\AgoraService::class, $agora);

        $source = LiveVideo::create(['title' => 'Source', 'status' => 'end', 'type' => 'live', 'user_id' => $this->subscribedOwnerId()]);
        $item = LiveVideoItem::create([
            'live_video_id' => $source->id,
            'title' => 'Unsold item',
            'quantity' => 1,
            'status' => 'pending',
        ]);

        [$target, $created] = app(AuctionItemTransferService::class)->createAuctionAndTransferItems(
            $source,
            [
                'type' => 'live',
                'title' => 'New live auction',
                'title_ar' => 'مزاد مباشر جديد',
            ],
            [$item->id]
        );

        $target = $target->fresh();

        $this->assertSame('live', $target->type);
        $this->assertNotEmpty($target->agora_app_id);
        $this->assertNotEmpty($target->agora_channel_name);
        $this->assertNotEmpty($target->agora_token);
        $this->assertNotEmpty($created->first()->live_video_id);
    }

    /** @dataProvider mismatchedTypes */
    public function test_service_rejects_mismatches_without_creating_copies_or_auctions(string $sourceType, string $targetType): void
    {
        $source = LiveVideo::create(['title' => 'Source', 'status' => 'end', 'type' => $sourceType]);
        $target = LiveVideo::create(['title' => 'Target', 'status' => 'pending', 'type' => $targetType]);
        $item = LiveVideoItem::create(['live_video_id' => $source->id, 'quantity' => 1]);
        $service = app(AuctionItemTransferService::class);
        $this->assertCount(0, $service->transferableItems($source, $target));
        $count = LiveVideo::count();
        foreach (['existing', 'new'] as $mode) {
            try {
                if ($mode === 'existing') {
                    $service->transferItems($source, $target, [$item->id]);
                } else {
                    $service->createAuctionAndTransferItems($source, ['type' => $targetType], [$item->id]);
                }
                $this->fail('Mismatched auction types must be rejected.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey($mode === 'existing' ? 'target_auction_id' : 'new_auction.type', $exception->errors());
            }
        }
        $this->assertSame($count, LiveVideo::count());
        $this->assertSame(0, $item->transferredCopies()->count());
    }

    public static function mismatchedTypes(): array
    {
        return [['live', 'recorded'], ['recorded', 'live']];
    }

    public function test_new_recorded_auction_preserves_type_and_transfers_pieces(): void
    {
        $source = LiveVideo::create(['title' => 'Source', 'status' => 'end', 'type' => 'recorded', 'user_id' => $this->subscribedOwnerId()]);
        $item = LiveVideoItem::create(['live_video_id' => $source->id, 'quantity' => 1]);
        $item->pieces()->create(['piece_number' => 1, 'identifier' => 'recorded-piece']);
        [$target, $copies] = app(AuctionItemTransferService::class)->createAuctionAndTransferItems($source, [], [$item->id]);
        $this->assertSame('recorded', $target->type);
        $this->assertSame('recorded-piece', $copies->first()->pieces->first()->identifier);
        $this->assertSame(0, $source->currentVideoItems()->count());
        $this->assertSame(1, $target->currentVideoItems()->count());
    }
}
