<?php

namespace App\Http\Resources;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class GiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id ??'',
            'name' => $this->name ?? '-',
            'coin' => $this->coin ?? '-',
            'image_svg' => Storage::disk('public')->url($this->image_svg)  ?? '-',
            'image_png' => Storage::disk('public')->url($this->image_png)  ?? '-',

        ];

        return $data;
    }
}
