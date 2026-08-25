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

        $source = LiveVideo::create(['title' => 'Source', 'status' => 'end', 'type' => 'recorded']);
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
}
