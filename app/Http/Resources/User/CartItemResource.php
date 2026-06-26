<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        $files = [];
        if (! empty($this->image) && is_array(json_decode($this->image))) {
            $index = 0;
            foreach (json_decode($this->image) as $feature) {
                $index++;
                $files[] = [
                    'key' => $index,
                    'file' => \Illuminate\Support\Facades\Storage::disk('public')->url($feature),
                ];
            }
        } else {
            $files[] = [
                'key' => 1,
                'file' => \Illuminate\Support\Facades\Storage::disk('public')->url($this->image),
            ];
        }

        $order = $this->order;

        return [
            'id' => $this->id,
            // 'order_id' => $order?->id,
            'title' => $this->title ?? '',
            // 'cart_status' => $order?->status_cart ?? 'pending',
            // 'payment_status' => $order?->payment_status ?? 'unpaid',
            'status' => $this->status,
            'image' => $files,
            'end_at' => $this->end_at,
            // 'category' => new CategoryResource($this->categoryData),
            'information' => $this->information,
            // 'weight' => $this->primaryPiece()?->weight,
            // 'age' => $this->primaryPiece()?->age,
            'quantity' => $this->quantity,
            'pieces' => VideoItemPieceResource::collection($this->pieces),
            // 'start_price' => $this->videoLive->start_price ?? $this->start_price,
            // 'bidding' => $this->bidding,
            'finished_price' => $this->finished_price,
            'user' => new UserDataResource($this->videoLive->user_Video),
            'user_take_auction' => new UserDataResource($this->user_auction),
        ];
    }
}
