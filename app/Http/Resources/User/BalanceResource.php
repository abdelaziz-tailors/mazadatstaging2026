<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use App\Models\LiveVideo;
use App\Models\VideoComment;
use App\Services\OrderService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {


        // if($this->isfollow ){
        //     $follow=true;
        // }else{
        //     $follow=false;

        // }
        // if ($this->resource['phone'] == null) {
        //     $profile_completed=false;
        // }else{
        //     $profile_completed=true;

        // }


        // Buyers owe money for unpaid orders; sellers/vendors are owed money
        // for sold items not yet settled/paid out. A buyer_vendor account can
        // do both, so its dues combine both sides.
        $dues = 0.0;
        if (in_array($this->user_type, ['buyer', 'buyer_vendor'], true)) {
            $dues += OrderService::unpaidBuyerTotal($this->id);
        }
        if (in_array($this->user_type, ['seller', 'vendor', 'buyer_vendor'], true)) {
            $dues += OrderService::unsettledSellerNet($this->id);
        }

        $data = [
            'balance' => $this->wallet_balance ?? 0,
            'dues' => round($dues, 2),
            // Explicit, unambiguous aliases so the app never has to derive
            // "available" itself (e.g. via balance - dues, which goes
            // negative whenever dues exceed the current balance — pending
            // dues aren't a deduction from the wallet, they just haven't
            // been credited to it yet). available_balance is always the
            // same value as "balance"; pending_balance is always the same
            // value as "dues".
            'available_balance' => $this->wallet_balance ?? 0,
            'pending_balance' => round($dues, 2),
            'active_bids_count' => $this->myActiveAuctionsCount(),
            // 'coin'=> $this->user_coin->coin ?? 0,
            // 'live-video'=> VideoGiftResource::collection($this->user_live_video_gifts)

        ];

        return $data;
    }

    /**
     * Count of this user's own currently in-progress auctions, computed
     * fresh on every request (no caching) — role-dependent, and a
     * buyer_vendor account combines the organizer and buyer sides:
     * - Organizer ("مشترك", user_type vendor/buyer_vendor): auctions they created (LiveVideo.user_id).
     * - Buyer (user_type buyer/buyer_vendor): auctions they've placed at least one bid in
     *   (a VideoComment row — "auctions/add" is this app's actual bid-placement endpoint;
     *   video_id on that table is the LiveVideo's own id, set directly in AuctionVideoController::add()).
     * - Seller (user_type seller): auctions containing at least one item they're the consignor for.
     */
    private function myActiveAuctionsCount(): int
    {
        $activeAuctionIds = collect();

        if (in_array($this->user_type, ['vendor', 'buyer_vendor'], true)) {
            $activeAuctionIds = $activeAuctionIds->merge(
                LiveVideo::where('status', 'start')->where('user_id', $this->id)->pluck('id')
            );
        }

        if (in_array($this->user_type, ['buyer', 'buyer_vendor'], true)) {
            $biddingAuctionIds = VideoComment::where('user_id', $this->id)->pluck('video_id');
            $activeAuctionIds = $activeAuctionIds->merge(
                LiveVideo::where('status', 'start')->whereIn('id', $biddingAuctionIds)->pluck('id')
            );
        }

        if ($this->user_type === 'seller') {
            $activeAuctionIds = $activeAuctionIds->merge(
                LiveVideo::where('status', 'start')
                    ->whereHas('video_items', function ($query) {
                        $query->where('seller_id', $this->id);
                    })
                    ->pluck('id')
            );
        }

        return $activeAuctionIds->unique()->count();
    }
}
