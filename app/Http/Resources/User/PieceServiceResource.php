<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class PieceServiceResource extends JsonResource
{
    public function toArray($request)
    {
        $customName = json_decode($this->custom_name, true);
        $catalog = $this->itemService;

        return [
            'id' => $this->id,
            'order_item_id' => $this->order_item_id,
            'live_video_item_id' => $this->orderItem?->live_video_item_id,
            'item_service_id' => $this->item_service_id,
            'name' => $this->displayName(),
            'name_en' => $catalog
                ? (json_decode($catalog->name, true)['en'] ?? $this->displayName('en'))
                : (is_array($customName) ? ($customName['en'] ?? '') : ''),
            'name_ar' => $catalog
                ? (json_decode($catalog->name, true)['ar'] ?? $this->displayName('ar'))
                : (is_array($customName) ? ($customName['ar'] ?? '') : ''),
            'price' => $this->price,
        ];
    }
}
