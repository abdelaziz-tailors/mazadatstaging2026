<?php

namespace App\Services;

use App\Http\Controllers\FirebaseController;
use App\Models\{LiveVideo, LiveVideoItem, VideoComment};
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\{DB, Log};

class AuctionAwardService
{
    public function award(int $itemId, ?int $bidId, ?int $actorId, bool $close = false): LiveVideoItem
    {
        try {
        return DB::transaction(function () use ($itemId, $bidId, $actorId, $close) {
            $item = LiveVideoItem::without('pieces')->whereKey($itemId)->lockForUpdate()->first();
            $auction = $item ? LiveVideo::whereKey($item->live_video_id)->lockForUpdate()->first() : null;
            if (! $item || ! $auction || $item->deleted_at || $auction->deleted_at) {
                $this->reject('Auction item not found.');
            }
            if ($actorId !== null && (int) $auction->user_id !== $actorId) {
                $this->reject('Only the auction organizer may award this item.', 403);
            }
            if ($close) {
                $item->update(['status' => 'finished', 'end_at' => now()]);
            }
            $highest = VideoComment::where('live_video_item_id', $item->id)
                ->where('video_id', $auction->id)->whereNull('comment_id')
                ->whereRaw("comment REGEXP '^[0-9]+([.][0-9]+)?$'")
                ->orderByRaw('CAST(comment AS DECIMAL(20, 2)) DESC')
                ->orderByDesc('id')->lockForUpdate()->first();

            // Preserve legacy already-awarded items during scheduled closure.
            if ($close && $item->user_finished_id !== null) {
                OrderService::attachWonItem($item);
                return $item;
            }
            if (! $highest || ! $highest->user_id) {
                if ($close && $bidId === null) { return $item; }
                $this->reject('No eligible bid exists for this item.');
            }
            if ($bidId !== null && (int) $highest->id !== $bidId) {
                $this->reject('The selected bid is stale or belongs to another item. Refresh the highest bid.');
            }
            if ($item->user_finished_id !== null || $item->finished_price !== null) {
                if ((int) $item->user_finished_id !== (int) $highest->user_id
                    || (float) $item->finished_price !== (float) $highest->comment) {
                    $this->reject('This item has already been awarded. Its winner cannot be changed.');
                }
                OrderService::attachWonItem($item);
                return $item; // Idempotent retry: no duplicate winner publication.
            }
            $item->update(['finished_price' => $highest->comment, 'user_finished_id' => $highest->user_id]);
            OrderService::attachWonItem($item);
            if (! $close) {
                DB::afterCommit(function () use ($highest) {
                    try { app(FirebaseController::class)->UserAuctions($highest); }
                    catch (\Throwable $e) {
                        Log::warning('Committed auction award could not be published.', ['bid_id' => $highest->id]);
                    }
                });
            }
            return $item;
        }, 3);
        } catch (\DomainException $e) {
            $this->reject($e->getMessage());
        }
    }

    private function reject(string $message, int $status = 200): void
    {
        throw new HttpResponseException(response()->json(['success' => false, 'code' => $status, 'message' => $message], $status));
    }
}