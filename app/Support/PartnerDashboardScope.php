<?php

namespace App\Support;

use App\Models\Category;
use App\Models\LiveVideo;
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
}
