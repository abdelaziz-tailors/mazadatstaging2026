<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerInvoiceItemResource extends JsonResource
{
    /**
     * Line item for vendors / stream partners (lot owner user_id or live partner_id).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $live = $this->videoLive;
        $finished = (float) ($this->finished_price ?? 0);
        $commissionPct = (float) ($live?->commission_amount ?? 0);
        $commissionAmount = (($live?->commission_payer ?? '') === 'seller')
            ? $commissionPct * $finished / 100
            : 0;
        $serviceFee = (float) ($live?->service_fee ?? 0);
        $netPrice =  $commissionAmount + $serviceFee;

        return [
            'id' => $this->id,
            'invoice_id' => $this->live_video_id . '-' . $this->id,
            'auction' => [
                'id' => $this->live_video_id,
                'title_en' => $live->title ?? '',
                'title_ar' => $live->title_ar ?? '',
            ],
            'title' => app()->getLocale() === 'en' ? ($this->title ?? '') : ($this->title_ar ?? ''),
            'title_en' => $this->title ?? '',
            'title_ar' => $this->title_ar ?? '',
            'status' => $this->status_cart,
            'payment_status' => $this->payment_status,
            'date' => $this->end_at,
            'price' => $this->finished_price,
            'commission_payer' => $live?->commission_payer,
            'commission_amount' => $commissionAmount,
            'service_fee_amount' => $serviceFee,
            'net_price' => $netPrice,
        ];
    }
}
