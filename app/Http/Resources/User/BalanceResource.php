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


        $balance = (float) ($this->wallet_balance ?? 0);
        $dues = 0.0;

        // For an auction partner, use the same Partner Earnings calculation
        // used by the partner invoice/wallet.
        $partnerAdminIds = \App\Models\Admin::query()
            ->where('type', 'partner')
            ->where('user_id', $this->id)
            ->pluck('id');

        if ($partnerAdminIds->isNotEmpty()) {

            $liveVideos = LiveVideo::query()
                ->where(function ($query) use ($partnerAdminIds) {
                    $query->where('user_id', $this->id)
                        ->orWhere('partner_id', $this->id)
                        ->orWhereIn('admin_id', $partnerAdminIds)
                        ->orWhereIn('partner_id', $partnerAdminIds);
                })
                ->whereHas('orders.items', fn ($q) => $q->whereNotNull('seller_id'))
                ->get();

            $paidPartnerEarnings = 0.0;
            $pendingPartnerEarnings = 0.0;

            foreach ($liveVideos as $liveVideo) {

                $sellerSummaries =
                    OrderService::sellerInvoiceSummariesForLiveVideo($liveVideo);

                $paymentStatuses =
                    $liveVideo->orders->pluck('payment_status', 'id');

                foreach ($sellerSummaries as $summary) {

                    $isOwnPiece =
                        (int) $summary['seller_id'] === (int) $this->id;

                    foreach ($summary['lines'] as $line) {

                        // Own piece = full sale price.
                        // External seller piece =
                        // commission + service fee + piece services.
                        $partnerEarnings = $isOwnPiece
                            ? round((float) $line['price'], 2)
                            : round(
                                (float) $line['commission']
                                + (float) $line['service_fee']
                                + (float) $line['piece_services'],
                                2
                            );

                        $paymentStatus =
                            $paymentStatuses->get($line['order_id']) ?? 'unpaid';

                        if ($paymentStatus === 'paid') {
                            $paidPartnerEarnings += $partnerEarnings;
                        } else {
                            $pendingPartnerEarnings += $partnerEarnings;
                        }
                    }
                }
            }

            $balance = round($paidPartnerEarnings, 2);
            $dues = round($pendingPartnerEarnings, 2);

        } elseif (\App\Models\OrderItem::query()->where('seller_id', $this->id)->exists()) {

            // Seller wallet.
            // Selling earnings are independent from anything this same user
            // buys in the auction.
            $sellerWallet = OrderService::sellerWalletSummary((int) $this->id);

            $balance = $sellerWallet['balance'];
            $dues = $sellerWallet['pending_balance'];

        } elseif (in_array($this->user_type, ['buyer', 'buyer_vendor'], true)) {

            // Pure buyer:
            // current balance is always zero.
            // "available" is used by mobile as total paid purchases.
            // pending/dues is the value of unpaid purchases.
            $balance = 0.0;
            $dues = OrderService::unpaidBuyerOrdersSubtotal((int) $this->id);

        } else {

            // Existing behavior for any remaining account type.
            if (in_array($this->user_type, ['seller', 'vendor', 'buyer_vendor'], true)) {
                $dues += OrderService::unsettledSellerNet($this->id);
            }
        }

        $data = [
            'balance' => $balance,
            'dues' => round($dues, 2),
            'available_balance' => in_array($this->user_type, ['buyer', 'buyer_vendor'], true)
                && $partnerAdminIds->isEmpty()
                && ! \App\Models\OrderItem::query()->where('seller_id', $this->id)->exists()
                    ? OrderService::paidBuyerOrdersTotal((int) $this->id)
                    : $balance,
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
