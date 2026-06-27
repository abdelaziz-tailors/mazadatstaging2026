<?php

namespace App\Http\Resources\User;

use App\Services\PieceServiceService;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerInvoiceItemResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $pieceServices = PieceServiceService::sumItemServicesForLiveVideoItem($this->resource);

        if ($this->videoLive->commission_payer == 'seller') {
            $commission = $this->videoLive->commission_amount * $this->finished_price / 100;
            $service_fee = $this->videoLive->service_fee;
            $net_price = $this->finished_price - $commission - $service_fee - $pieceServices;
        } else {
            $commission = 0;
            $service_fee = $this->videoLive->service_fee;
            $net_price = $this->finished_price - $service_fee - $pieceServices;
        }

        return [
            'id' => $this->resource['id'] ?? '',
            'order_id' => $this->order?->id,
            'order_number' => $this->order?->order_number,
            'invoice_id' => $this->order ? $this->order->invoiceId() : ($this->resource['live_video_id'] . '-' . $this->resource['id']),
            'title' => app()->getLocale() == 'en' ? ($this['title'] ?? '') : ($this['title_ar'] ?? ''),
            'title_en' => $this->title ?? '',
            'title_ar' => $this->title_ar ?? '',
            'price' => $this->finished_price,
            'commission_payer' => $this->videoLive->commission_payer,
            'commission' => $this->videoLive->commission_amount . '%',
            'commission_amount' => $commission,
            'service_fee_amount' => $service_fee,
            'piece_services_amount' => $pieceServices,
            'net_price' => $net_price,
        ];
    }
}
