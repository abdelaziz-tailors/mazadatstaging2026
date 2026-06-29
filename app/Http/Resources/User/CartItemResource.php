<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        $item = $this->liveVideoItem;

        $files = [];
        if ($item && ! empty($item->image) && is_array(json_decode($item->image))) {
            $index = 0;
            foreach (json_decode($item->image) as $feature) {
                $index++;
                $files[] = [
                    'key' => $index,
                    'file' => \Illuminate\Support\Facades\Storage::disk('public')->url($feature),
                ];
            }
        } elseif ($item && ! empty($item->image)) {
            $files[] = [
                'key' => 1,
                'file' => \Illuminate\Support\Facades\Storage::disk('public')->url($item->image),
            ];
        }

        $hasSeller = $item?->seller_id !== null;

        return [
            'order_item_id' => $this->id,
            'id' => $item?->id,
            'title' => $item?->title ?? '',
            'status' => $item?->status,
            'image' => $files,
            'end_at' => $item?->end_at,
            'information' => $item?->information,
            'quantity' => $item?->quantity,
            'pieces' => VideoItemPieceResource::collection($item?->pieces ?? collect()),
            'can_add_services' => $hasSeller,
            'services' => $this->when(
                $hasSeller && $this->relationLoaded('services'),
                fn () => PieceServiceResource::collection($this->services)
            ),
            'services_total' => $this->when(
                $hasSeller && $this->relationLoaded('services'),
                fn () => round((float) $this->services->sum('price'), 2)
            ),
            'finished_price' => $this->finished_price,
            'user' => $item?->videoLive ? new UserDataResource($item->videoLive->user_Video) : null,
            'user_take_auction' => $item?->user_auction ? new UserDataResource($item->user_auction) : null,
        ];
    }
}
