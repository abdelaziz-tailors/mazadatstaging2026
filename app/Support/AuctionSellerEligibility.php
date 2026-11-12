<?php

namespace App\Support;

use App\Models\LiveVideo;
use App\Models\SellerSubmission;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;

final class AuctionSellerEligibility
{
    public static function normalizeOwnerId($sellerId, ?LiveVideo $auction, $ownerId)
    {
        if ($auction && $ownerId && (int) $auction->user_id === (int) $ownerId
            && in_array($sellerId, [0, '0', (int) $ownerId, (string) $ownerId], true)) {
            return null;
        }

        return $sellerId;
    }

    public static function nullableIdRules(LiveVideo $auction): array
    {
        return ['bail', 'nullable', function ($attribute, $value, $fail) {
            if (! is_int($value) && ! is_string($value)) {
                $fail(trans('validation.integer', ['attribute' => $attribute]));
            }
        }, 'integer', self::rule($auction)];
    }

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
