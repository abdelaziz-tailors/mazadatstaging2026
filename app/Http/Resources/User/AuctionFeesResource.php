<?php

namespace App\Http\Resources\User;

use App\Models\LiveVideo;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionFeesResource extends JsonResource
{
    public function toArray($request)
    {
        $showCommission = $this->canViewCommission($request);

        return [
            'commission_amount' => $showCommission ? $this->commission_amount : null,
            'commission_payer' => $showCommission ? $this->commission_payer : null,
            'service_fee' => $this->service_fee,
        ];
    }

    /** Shared by auction JSON and the buyer invoice's derived commission row. */
    public function canViewCommission($request): bool
    {
        $viewerId = $request->user('api')?->getAuthIdentifier();
        // HomeVideoResource is also reused for ordinary videos, which have no auction fees.
        if (! $viewerId || ! ($this->resource instanceof LiveVideo)
            || ! in_array($this->commission_payer, ['buyer', 'seller'], true)) {
            return false;
        }

        // Preserve subscriber access independently of the actual item's consignor.
        return $this->commission_payer === 'buyer'
            || (int) $this->user_id === (int) $viewerId
            || $this->video_items()->where('seller_id', $viewerId)->exists();
    }
}
