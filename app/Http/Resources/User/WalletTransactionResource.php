<?php

namespace App\Http\Resources\User;

use App\Helpers\TranslationHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'balance_after' => $this->balance_after !== null ? (float) $this->balance_after : null,
            // Stored as literal English text (see AuctionWalletSettlement::applyDelta) —
            // translated here per the request's Accept-Language so it comes back
            // Arabic instead of always-English, same as every other user-facing string.
            'description' => $this->description ? TranslationHelper::translate($this->description) : $this->description,
            'order_number' => $this->whenLoaded('order', fn () => $this->order->order_number ?? null),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
