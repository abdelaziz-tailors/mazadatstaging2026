<?php

namespace App\Support;

use App\Models\Category;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\SellerSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PartnerDashboardScope
{
    public static function isPartner(): bool
    {
        $admin = Auth::guard('admin')->user();

        return $admin && $admin->type === 'partner';
    }

    /**
     * Limit LiveVideo queries to auctions owned by the current partner admin.
     */
    public static function scopeLiveVideos(Builder $query): Builder
    {
        if (self::isPartner()) {
            $query->where('admin_id', Auth::guard('admin')->user()->id);
        }

        return $query;
    }

    /**
     * Limit Order queries to auctions owned by the current partner admin.
     */
    public static function scopeOrders(Builder $query): Builder
    {
        if (self::isPartner()) {
            $query->whereHas('liveVideo', function (Builder $q) {
                $q->where('admin_id', Auth::guard('admin')->user()->id);
            });
        }

        return $query;
    }

    public static function ensureOwnOrder(Order $order): void
    {
        if (! self::isPartner()) {
            return;
        }

        $order->loadMissing('liveVideo');

        if (! $order->liveVideo) {
            abort(403, 'Unauthorized access.');
        }

        self::ensureOwnLiveVideo($order->liveVideo);
    }

    /**
     * Limit LiveVideoItem queries to items under this partner's live streams.
     */
    public static function scopeLiveVideoItems(Builder $query): Builder
    {
        if (self::isPartner()) {
            $query->whereHas('videoLive', function (Builder $q) {
                $q->where('admin_id', Auth::guard('admin')->user()->id);
            });
        }

        return $query;
    }

    /**
     * Partner may only manage their own live auctions.
     */
    public static function ensureOwnLiveVideo(LiveVideo $video): void
    {
        if (self::isPartner() && (int) $video->admin_id !== (int) Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Categories created under this partner (dashboard categories table uses admin_id).
     */
    public static function scopeCategories(Builder $query): Builder
    {
        if (self::isPartner()) {
            $query->where('admin_id', Auth::guard('admin')->user()->id);
        }

        return $query;
    }

    public static function ensureOwnCategory(Category $category): void
    {
        if (! self::isPartner()) {
            return;
        }
        $ownerId = (int) Auth::guard('admin')->user()->id;
        if ((int) ($category->admin_id ?? 0) !== $ownerId) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Submissions addressed to this partner (partner_id is users.id linked via admins.user_id).
     */
    public static function scopeSellerSubmissions(Builder $query): Builder
    {
        if (self::isPartner()) {
            $partnerUserId = Auth::guard('admin')->user()->user_id;
            if (! $partnerUserId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('partner_id', $partnerUserId);
            }
        }

        return $query;
    }

    public static function ensureOwnSellerSubmission(SellerSubmission $submission): void
    {
        if (! self::isPartner()) {
            return;
        }
        $partnerUserId = Auth::guard('admin')->user()->user_id;
        if (! $partnerUserId || (int) $submission->partner_id !== (int) $partnerUserId) {
            abort(403, 'Unauthorized access.');
        }
    }

    public static function scopeItemServices(Builder $query): Builder
    {
        if (self::isPartner()) {
            $query->where('admin_id', Auth::guard('admin')->user()->id);
        }

        return $query;
    }

    public static function ensureOwnItemService(\App\Models\ItemService $service): void
    {
        if (! self::isPartner()) {
            return;
        }

        if ((int) ($service->admin_id ?? 0) !== (int) Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }
    }

    public static function isSuperAdmin(): bool
    {
        $admin = Auth::guard('admin')->user();

        return $admin && $admin->type !== 'partner';
    }
}
