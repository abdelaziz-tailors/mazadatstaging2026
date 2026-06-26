<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProofResource extends JsonResource
{
    public function toArray($request)
    {
        $proof = $this->order?->payment_proof;

        return [
            'id' => $this->id,
            'order_id' => $this->order?->id,
            'order_number' => $this->order?->order_number,
            'auction_item_id' => $this->id,
            'payment_proof' => $proof ? asset($proof) : null,
        ];
    }
}
