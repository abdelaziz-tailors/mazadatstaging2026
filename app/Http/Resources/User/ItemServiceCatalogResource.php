<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemServiceCatalogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->localizedName(),
            'name_en' => json_decode($this->name, true)['en'] ?? '',
            'name_ar' => json_decode($this->name, true)['ar'] ?? '',
            'default_price' => $this->default_price,
        ];
    }
}
