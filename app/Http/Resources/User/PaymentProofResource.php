<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProofResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'auction_item_id' => $this->id,
            'payment_proof' => $this->payment_proof
                ? asset($this->payment_proof)
                : null,
        ];
    }
}
