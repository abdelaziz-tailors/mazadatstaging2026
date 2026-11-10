<?php

namespace App\Support;

use App\Models\LiveVideoItem;
use App\Models\OrderItem;

final class AuctionItemFinancialGuard
{
    /** Caller must hold the item lock, then its auction lock, as award/order creation does. */
    public static function isCommitted(LiveVideoItem $item): bool
    {
        return $item->user_finished_id !== null || $item->finished_price !== null || $item->payment_status === 'paid'
            || OrderItem::where('live_video_item_id', $item->id)->lockForUpdate()->first() !== null;
    }
}
