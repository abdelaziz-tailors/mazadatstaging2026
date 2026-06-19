<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoItemPieceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'piece_number' => $this->piece_number,
            'age' => $this->age,
            'weight' => $this->weight,
            // 'piece_multiplier_number' => $this->piece_multiplier_number ?? '',
            'identifier' => $this->identifier ?? '',
            'baham_count' => $this->baham_count ?? '',
        ];
    }
}
