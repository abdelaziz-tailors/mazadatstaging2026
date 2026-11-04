<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoItemPieceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'piece_number' => $this->piece_number,
            'age' => $this->age,
            'gender' => $this->gender,
            // 'piece_multiplier_number' => $this->piece_multiplier_number ?? '',
            'identifier' => $this->identifier ?? '',
            'baham_count' => $this->baham_count ?? '',
        ];
    }
}
