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
            'total_price' => $this->total,
            'shipping_address' => $this->shipping_address ? [
                'address' => $this->shipping_address,
                'city_id' => $this->shipping_city_id,
                'city' => $this->shippingCityName(),
                'lat' => $this->shipping_lat,
                'lng' => $this->shipping_lng,
            ] : null,
            'payment_proof' => $this->payment_proof ? asset($this->payment_proof) : null,
        ];
    }

    /**
     * City.name is stored as a per-locale JSON blob ({"ar": ..., "en": ...}),
     * same convention as everywhere else this app resolves a localized name.
     */
    private function shippingCityName(): ?string
    {
        $city = $this->shippingCity;

        if (! $city) {
            return null;
        }

        $decoded = json_decode($city->name, true);

        if (is_array($decoded)) {
            return $decoded[app()->getLocale()] ?? $decoded['ar'] ?? null;
        }

        return $city->name;
    }
}
