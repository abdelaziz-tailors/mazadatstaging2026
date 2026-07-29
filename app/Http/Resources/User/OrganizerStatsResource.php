<?php

namespace App\Http\Resources\User;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'total_buyers' => $this->ordersQuery()->distinct('buyer_id')->count('buyer_id'),
            'pending_commissions' => round($this->ordersQuery()->where('payment_status', 'unpaid')->sum('commission_value'), 2),
            'monthly_profit' => round(
                $this->ordersQuery()
                    ->where('payment_status', 'paid')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->sum('commission_value'),
                2
            ),
            'total_sellers' => $this->itemsQuery()->whereNotNull('seller_id')->distinct('seller_id')->count('seller_id'),
            'total_auctions' => LiveVideo::where('user_id', $this->id)->count(),
            'total_products' => $this->itemsQuery()->count(),
            'total_sales' => round($this->itemsQuery()->sum('finished_price'), 2),
        ];
    }

    /**
     * Orders placed on auctions THIS organizer created (LiveVideo.user_id).
     * Built fresh on every call so the separate aggregate queries above
     * don't share/mutate the same builder instance.
     */
    private function ordersQuery()
    {
        return Order::whereHas('liveVideo', function ($query) {
            $query->where('user_id', $this->id);
        });
    }

    /**
     * Items belonging to auctions THIS organizer created (LiveVideo.user_id).
     * Built fresh on every call for the same reason as ordersQuery().
     */
    private function itemsQuery()
    {
        return LiveVideoItem::whereHas('videoLive', function ($query) {
            $query->where('user_id', $this->id);
        });
    }
}
