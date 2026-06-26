<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserInvoiceResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $live = $this->liveVideo;
        $lineItems = $this->items->map(fn ($orderItem) => $orderItem->liveVideoItem)->filter();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'live_video_id' => $this->live_video_id,
            'title' =>$live->title_ar,
            'live_status' => $live->status ?? '',
            'end_at' => $live->end_at ?? null,
            'sub_total' => $this->subtotal,
            'tax' => $this->tax_value,
            'commission' => $this->commission_value,
            'total_price' => $this->total,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'total_items' => $lineItems->count(),
            'video_items' => ProviderInvoiceItemResource::collection($lineItems),
        ];
    }
}
