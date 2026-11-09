<?php

namespace App\Console\Commands;

use App\Services\AuctionBidWithdrawalSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncWithdrawnAuctionBids extends Command
{
    protected $signature = 'auctions:sync-withdrawn-bids';
    protected $description = 'Retry pending withdrawn bid removals from Firebase';

    public function handle(AuctionBidWithdrawalSync $sync)
    {
        DB::table('auction_bid_withdrawals')->whereNull('synced_at')->orderBy('bid_id')
            ->chunkById(100, function ($entries) use ($sync) {
                foreach ($entries as $entry) { $sync->sync($entry->bid_id); }
            }, 'bid_id');
        // Placement publication failures also retain a durable dirty version.
        DB::table('live_video_items')->whereColumn('bid_state_version', '>', 'bid_state_synced_version')
            ->whereNull('deleted_at')->orderBy('id')->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    try {
                        app(\App\Http\Controllers\FirebaseController::class)->syncAuctionBidState($item->live_video_id, $item->id);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Auction bid state pending retry.', ['item_id' => $item->id]);
                    }
                }
            });
        return 0;
    }
}
