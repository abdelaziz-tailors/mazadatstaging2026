<?php

namespace App\Console\Commands;

use App\Jobs\ReconcileAuctionRecording;
use App\Models\AuctionRecording;
use App\Models\LiveVideo;
use Illuminate\Console\Command;

class ReconcileAuctionRecordings extends Command
{
    protected $signature = 'recordings:reconcile';
    protected $description = 'Reconcile recording lifecycle and verify expired media deletion';

    public function handle()
    {
        if (!config('recording.enabled')) {
            return 0;
        }

        // Includes tombstones: late uploads must also be removed on subsequent sweeps.
        AuctionRecording::where(function ($query) {
            $query->whereNull('retry_at')->orWhere('retry_at', '<=', now());
        })->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                dispatch(new ReconcileAuctionRecording($row->live_video_id));
            }
        });

        LiveVideo::where('status', 'start')->where('type', 'live')
            ->whereNotNull('agora_channel_name')
            ->whereNotIn('id', AuctionRecording::select('live_video_id'))
            ->chunkById(100, function ($videos) {
                foreach ($videos as $video) {
                    dispatch(new ReconcileAuctionRecording($video->id));
                }
            });

        return 0;
    }
}
