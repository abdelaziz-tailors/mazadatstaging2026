<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class AuctionWinVideoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'auction_item_id' => $this->id,
            // 'title' => app()->getLocale() === 'ar' ? ($this->title_ar ?? $this->title) : ($this->title ?? $this->title_ar),
            // 'title_en' => $this->title ?? '',
            // 'title_ar' => $this->title_ar ?? '',
            // 'finished_price' => $this->finished_price,
            // 'end_at' => $this->end_at,
            'winner_video' => $this->winner_video
                ? asset($this->winner_video)
                : null,
        ];
    }
}
