<?php

namespace App\Services;

use App\Models\{LiveVideo, LiveVideoItem, VideoComment, VideoCommentLike};
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class AuctionBidWithdrawalService
{
    public function withdraw(int $bidId, int $userId): void
    {
        if ($userId <= 0) { $this->reject('Un Authenticated', 401); }
        // This lookup only locates the lock; all decisions use locked current reads.
        $itemId = VideoComment::whereKey($bidId)->value('live_video_item_id');
        if (! $itemId) { $this->reject('Bid not found.', 404); }

        DB::transaction(function () use ($bidId, $userId, $itemId) {
            $item = LiveVideoItem::without('pieces')->whereKey($itemId)->lockForUpdate()->first();
            $auction = $item ? LiveVideo::whereKey($item->live_video_id)->sharedLock()->first() : null;
            $bid = VideoComment::whereKey($bidId)->lockForUpdate()->first();
            if (! $bid || $bid->comment_id !== null || (int) $bid->live_video_item_id !== (int) $itemId) {
                $this->reject('Bid not found.', 404);
            }
            if ((int) $bid->user_id !== $userId) { $this->reject('Only the bidder may withdraw this bid.', 403); }
            if (! $item || ! $auction || $item->deleted_at || $auction->deleted_at
                || (int) $bid->video_id !== (int) $auction->id) {
                $this->reject('Auction integrity cannot be guaranteed.', 409);
            }
            if ($auction->status !== 'start' || $item->status !== 'working'
                || $item->finished_price !== null || $item->user_finished_id !== null
                || $item->orderItem()->lockForUpdate()->first()) {
                $this->reject('Withdrawals are closed for this item.', 409);
            }
            $history = VideoComment::where('live_video_item_id', $itemId)->whereNull('comment_id')
                ->whereRaw("comment REGEXP '^[0-9]+([.][0-9]+)?$'")
                ->orderByRaw('CAST(comment AS DECIMAL(20, 2)) DESC')->orderByDesc('id');
            $highest = (clone $history)->lockForUpdate()->first();
            $awardHighest = (clone $history)->where('video_id', $auction->id)->lockForUpdate()->first();
            if (! $highest || ! $awardHighest || $highest->id !== $awardHighest->id
                || ! $highest->user_id || ! preg_match('/^[0-9]{1,12}(?:\.[0-9]{1,2})?$/D', (string) $bid->comment)
                || ! preg_match('/^[0-9]{1,12}(?:\.[0-9]{1,2})?$/D', (string) $highest->comment)
                || (float) $highest->comment <= (float) $bid->comment) {
                $this->reject('Only strictly outbid bids with consistent price history may be withdrawn.', 409);
            }

            // Preserve audit history, including descendants of already-deleted replies.
            $ids = [$bid->id];
            $frontier = $ids;
            while ($frontier) {
                $frontier = VideoComment::withTrashed()->whereIn('comment_id', $frontier)
                    ->lockForUpdate()->pluck('id')->diff($ids)->values()->all();
                $ids = array_merge($ids, $frontier);
            }
            // Fail closed on malformed relationships rather than deleting another item's bids.
            if (VideoComment::withTrashed()->whereIn('id', $ids)->where('id', '!=', $bid->id)
                ->where(function ($query) use ($auction) {
                    $query->whereNotNull('live_video_item_id')->orWhere('video_id', '!=', $auction->id)
                        ->orWhereNull('video_id');
                })->exists()) {
                $this->reject('Reply integrity cannot be guaranteed.', 409);
            }
            VideoCommentLike::whereIn('comment_id', $ids)->delete();
            VideoComment::whereIn('id', $ids)->delete();
            DB::table('auction_bid_withdrawals')->insert([
                'bid_id' => $bid->id, 'auction_id' => $auction->id, 'item_id' => $item->id,
                'created_at' => now(), 'synced_at' => null,
            ]);
            DB::afterCommit(function () use ($bidId) {
                app(AuctionBidWithdrawalSync::class)->sync($bidId);
            });
        }, 3);
    }

    private function reject(string $message, int $status): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false, 'code' => $status, 'message' => $message,
        ], $status));
    }
}
