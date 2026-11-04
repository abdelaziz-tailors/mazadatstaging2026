<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class AuctionFeesResource extends JsonResource
{
    public function toArray($request)
    {
        $viewerId = $request->user('api')?->getAuthIdentifier();
        $isSeller = $viewerId && (
            (int) $this->user_id === (int) $viewerId
            || $this->video_items()->where('seller_id', $viewerId)->exists()
        );
        $showCommission = $viewerId && $this->commission_payer === ($isSeller ? 'seller' : 'buyer');

        return [
            'commission_amount' => $showCommission ? $this->commission_amount : null,
            'commission_payer' => $showCommission ? $this->commission_payer : null,
            'service_fee' => $this->service_fee,
        ];
    }
}
