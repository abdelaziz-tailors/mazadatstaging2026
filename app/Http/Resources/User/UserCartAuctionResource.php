<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCartAuctionResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $live = $this->liveVideo;

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            // 'invoice_id' => $this->invoiceId(),
            'order_id' => $this->id,
            'live_video_id' => $this->live_video_id,
            'title' => $live->title_ar ?? '',
            'items_cart' => CartItemResource::collection($this->whenLoaded('items')),
            'sub_total' => $this->subtotal,
            'tax' => $this->tax_value,
            'tax_amount' => $this->tax_percent.'%',
            'commission' => $this->commission_value,
            $this->mergeWhen($this->commission_payer === 'buyer', function () {
                return [
                    'commission_amount' => $this->commission_percent.'%',
                ];
            }),
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'piece_services_total' => $this->piece_services_total,
            'total_price' => $this->total,
        ];
    }
}
