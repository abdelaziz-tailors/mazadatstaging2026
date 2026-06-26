<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserInvoiceItemResource extends JsonResource
{
    public function toArray($request)
    {
        $order = $this->order;

        return [
            'id' => $this->id,
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
            'invoice_id' => $order ? $order->invoiceId() : ($this->live_video_id.'-'.$this->id),
            'title' => app()->getLocale() === 'en' ? ($this->title ?? '') : ($this->title_ar ?? ''),
            'title_en' => $this->title ?? '',
            'title_ar' => $this->title_ar ?? '',
            'status' => $order?->status ?? 'pending',
            'payment_status' => $order?->payment_status ?? 'unpaid',
            'date' => $this->end_at,
            'price' => $this->finished_price,
        ];
    }
}
