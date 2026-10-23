<?php

namespace App\Services;

use App\Http\Controllers\FirebaseController;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\VideoComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuctionBidService
{
    public function place(int $itemId, int $userId, $amount): VideoComment
    {
        return DB::transaction(function () use ($itemId, $userId, $amount) {
            $item = LiveVideoItem::without('pieces')->whereKey($itemId)->lockForUpdate()->first();
            // All reads in this transaction are current reads. In particular, do
            // not establish a pre-lock snapshot while looking up the parent ID.
            // A shared parent lock also serializes us with auction closure.
            $auction = $item ? LiveVideo::whereKey($item->live_video_id)->sharedLock()->first() : null;
            if (! $item || ! $auction || $item->deleted_at || $auction->deleted_at
                || (int) $item->live_video_id !== (int) $auction->id) {
                $this->reject('Auction item not found.');
            }

            // A locking (current) read is essential under MySQL REPEATABLE READ.
            // Replies and nonnumeric legacy comments are not bid state.
            $highest = VideoComment::where('live_video_item_id', $item->id)
                ->whereNull('comment_id')
                ->whereRaw("comment REGEXP '^[0-9]+([.][0-9]+)?$'")
                ->orderByRaw('CAST(comment AS DECIMAL(20, 2)) DESC')
                ->orderByDesc('id')->lockForUpdate()->first();
            $current = $highest ? $this->minorUnits($highest->comment)
                : $this->minorUnits($auction->start_price ?? $item->start_price ?? 0);
            $increment = $this->minorUnits($item->bidding ?? 0);
            if ($current === null || $increment === null) {
                $this->reject('Invalid auction price configuration.');
            }
            $minimum = $current + max(1, $increment);
            $price = ['current_price' => $this->decimal($current), 'next_minimum' => $this->decimal($minimum)];
            if ($auction->status !== 'start' || $item->status !== 'working'
                || $item->finished_price !== null || $item->user_finished_id !== null) {
                $this->reject('Bidding is closed for this item.', $price);
            }
            // Authentication remains the existing bidding permission contract.
            if ($userId <= 0) {
                $this->reject('Un Authenticated', $price);
            }
            $bid = $this->minorUnits($amount);
            if ($bid === null || $bid <= $current || $bid < $minimum) {
                $this->reject('Bid rejected. Current price is '.$price['current_price']
                    .'; next minimum is '.$price['next_minimum'].'.', $price);
            }
            // The accepted top-level history row IS the authoritative highest-bid
            // state. finished_price/user_finished_id remain award-only fields.
            $record = VideoComment::create([
                'video_id' => $auction->id,
                'live_video_item_id' => $item->id,
                'comment' => $this->decimal($bid),
                'user_id' => $userId,
            ]);
            DB::afterCommit(function () use ($record) {
                try {
                    app(FirebaseController::class)->AddAuctions($record);
                } catch (\Throwable $e) {
                    Log::warning('Committed auction bid could not be published.', ['bid_id' => $record->id]);
                }
            });

            return $record;
        }, 3);
    }

    private function minorUnits($value): ?int
    {
        if (! is_scalar($value) || ! preg_match('/^([0-9]{1,12})(?:\.([0-9]{1,2}))?$/D', (string) $value, $parts)) {
            return null;
        }

        return (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    private function decimal(int $value): string
    {
        return intdiv($value, 100).'.'.str_pad((string) ($value % 100), 2, '0', STR_PAD_LEFT);
    }

    private function reject(string $message, array $price = []): void
    {
        // Keep the existing HTTP 200 / success:false mobile API envelope.
        throw new HttpResponseException(response()->json([
            'success' => false, 'code' => 200, 'message' => $message, 'data' => $price,
        ]));
    }
}
