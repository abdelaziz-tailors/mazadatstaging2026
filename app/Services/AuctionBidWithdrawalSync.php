<?php

namespace App\Services;

use App\Http\Controllers\FirebaseController;
use Illuminate\Support\Facades\{DB, Log};

class AuctionBidWithdrawalSync
{
    public function sync(int $bidId): void
    {
        try {
            $entry = DB::table('auction_bid_withdrawals')->where('bid_id', $bidId)->whereNull('synced_at')->first();
            if (! $entry) { return; }
            // Idempotent, narrowly scoped removal; never rewrite a concurrent new bid.
            app(FirebaseController::class)->removeAuctionBid($entry->auction_id, $entry->item_id, $bidId);
            DB::table('auction_bid_withdrawals')->where('bid_id', $bidId)->update(['synced_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Withdrawn bid Firebase removal pending retry.', ['bid_id' => $bidId]);
        }
    }
}
