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
        return 0;
    }
}
