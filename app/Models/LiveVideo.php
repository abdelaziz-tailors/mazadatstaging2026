<?php

namespace App\Models;

use App\Models\User\User;
use App\Services\PieceServiceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LiveVideo extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function user_Video()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user_auction()
    {
        return $this->belongsTo(User::class, 'user_price_id');
    }
    public function partner()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function partnerData()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->select('id', 'name->'.app()->getLocale().' as name');;
    }

    public function video_items()
    {
        return $this->hasMany(LiveVideoItem::class, 'live_video_id');
    }

    public function isEnded(): bool
    {
        return $this->status === 'end';
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'live_video_id');
    }

    public function user_finished_items()
    {
        return $this->hasMany(LiveVideoItem::class, 'live_video_id')->where('user_finished_id', auth('api')->user()->id);
    }


    public function all_views()
    {
        return $this->hasMany(VideoView::class,'video_id');
    }

    public function likes()
    {
        return $this->hasMany(LiveVideoLike::class,'live_video_id');
    }

    public function islike()
    {
        // Check if there's a favorite record associated with this post and the current user
        if(isset(auth('api')->user()->id)){
            return $this->likes()->where('user_id', auth('api')->user()->id);

        }else{
            return $this->likes()->where('user_id', '');
        }
    }


    public function categoryData()
    {
        return $this->belongsTo(Category::class, 'category_id')->select('id', 'name->'.app()->getLocale().' as name');

    }



    public function gifts()
    {
        return $this->hasMany(UserGift::class, 'video_id');
    }


    public function live_video_user()
    {
        return $this->hasMany(LiveVideoUser::class, 'live_video_id');
    }
    public function leave_live_video_user()
    {
        return $this->hasMany(LiveVideoUser::class, 'live_video_id')->where('leave',1);
    }
    public function in_live_video_user()
    {
        return $this->hasMany(LiveVideoUser::class, 'live_video_id')->where('leave',0);
    }

    public function Live_Video_count()
    {
        return $this->HasMany(LiveVideoLike::class, 'live_video_id');
    }



    public function favorites()
    {
        return $this->hasMany(VideoFavorites::class, 'video_id');
    }

    public function isfavorite()
    {
        // Check if there's a favorite record associated with this post and the current user
        if(isset(auth('api')->user()->id)){
            return $this->favorites()->where('user_id', auth('api')->user()->id);

        }else{
            return $this->favorites()->where('user_id', '');
        }
    }


    public function sub_total(?int $buyerUserId = null): float
    {
        $buyerId = $buyerUserId;
        if ($buyerId === null && auth('api')->check()) {
            $buyerId = (int) auth('api')->user()->id;
        }
        if ($buyerId === null) {
            return 0.0;
        }

        return (float) $this->video_items()->where('user_finished_id', $buyerId)->sum('finished_price');
    }

    /**
     * Total sales for this auction across all buyers — the sum of finished_price
     * for every sold item (unsold items have a null finished_price, which SUM
     * ignores). Used by the organizer's "my auctions" list.
     */
    public function totalSales(): float
    {
        return (float) $this->video_items()->sum('finished_price');
    }

    public function tax_value(?int $buyerUserId = null): float
    {
        return (float) ($this->sub_total($buyerUserId) * ((float) ($this->tax_amount ?? 0)) / 100);
    }

    public function commission_value(?int $buyerUserId = null): float
    {
        if ($this->commission_payer == 'buyer') {
            return (float) ($this->sub_total($buyerUserId) * ((float) ($this->commission_amount ?? 0)) / 100);
        }

        return 0.0;
    }

    public function total_price(?int $buyerUserId = null): float
    {
        return (float) ($this->sub_total($buyerUserId) + $this->tax_value($buyerUserId) + $this->commission_value($buyerUserId));
    }

    /**
     * Per-winning-buyer subtotal, tax, commission, and total for this live (admin dashboard).
     */
    public function buyerOrderSummaries(): Collection
    {
        $ids = $this->video_items()
            ->whereNotNull('user_finished_id')
            ->pluck('user_finished_id')
            ->unique()
            ->values();

        return $ids->map(function ($buyerId) {
            $buyerId = (int) $buyerId;
            $user = User::query()->find($buyerId);

            return [
                'user_id' => $buyerId,
                'user_name' => $user->name ?? '—',
                'sub_total' => $this->sub_total($buyerId),
                'tax' => $this->tax_value($buyerId),
                'commission' => $this->commission_value($buyerId),
                'total' => $this->total_price($buyerId),
            ];
        });
    }



    /**
     * Per-consignor (seller) gross, platform commission, service fees, and net, for sold items with that seller_id.
     */
    public function sellerOrderSummaries(): Collection
    {
        $grouped = $this->video_items()
            ->whereNotNull('user_finished_id')
            ->whereNotNull('seller_id')
            ->with('orderItem.services')
            ->get()
            ->groupBy('seller_id');

        return $grouped->map(function ($rows, $sellerId) {
            $sellerId = (int) $sellerId;
            $seller = User::query()->find($sellerId);
            $gross = 0.0;
            $commissionTotal = 0.0;
            $serviceTotal = 0.0;
            $pieceServicesTotal = 0.0;
            $net = 0.0;
            $servicePerLine = (float) ($this->service_fee ?? 0);
            foreach ($rows as $item) {
                $finished = (float) ($item->finished_price ?? 0);
                $pieceServices = PieceServiceService::sumItemServicesForLiveVideoItem($item);
                $gross += $finished;
                if ($this->commission_payer == 'seller') {
                    $c = (float) ($this->commission_amount ?? 0) * $finished / 100;
                } else {
                    $c = 0.0;
                }
                $commissionTotal += $c;
                $serviceTotal += $servicePerLine;
                $pieceServicesTotal += $pieceServices;
                $net += $finished - $c - $servicePerLine - $pieceServices;
            }

            return [
                'seller_id' => $sellerId,
                'seller_name' => $seller->name ?? '—',
                'gross' => $gross,
                'commission' => $commissionTotal,
                'service_fee' => $serviceTotal,
                'piece_services' => $pieceServicesTotal,
                'net' => $net,
            ];
        })->values();
    }

    public function sellerOrderSummaryForSellerId(?int $sellerId): ?array
    {
        if (! $sellerId) {
            return null;
        }

        return $this->sellerOrderSummaries()
            ->firstWhere('seller_id', (int) $sellerId);
    }

}
