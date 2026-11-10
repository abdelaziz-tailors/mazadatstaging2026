<?php

namespace App\Support;

use App\Models\LiveVideo;
use App\Models\SellerSubmission;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;

final class AuctionSellerEligibility
{
    public static function forAuction(LiveVideo $auction): Builder
    {
        // Submission partner_id is a user ID. Auction partner_id may be a
        // legacy admin ID, so use the auction's explicit organizer user_id.
        return User::query()->where('user_type', 'seller')->where('is_active', 1)
            ->whereIn('id', SellerSubmission::query()->select('user_id')
                ->where('partner_id', (int) $auction->user_id)->where('status', 'approved'));
    }

    public static function rule(LiveVideo $auction): \Closure
    {
        return function ($attribute, $value, $fail) use ($auction) {
            if (! self::forAuction($auction)->whereKey($value)->exists()) {
                $fail(trans('validation.exists', ['attribute' => $attribute]));
            }
        };
    }
}
