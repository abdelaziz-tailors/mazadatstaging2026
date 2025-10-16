<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class SoundResource extends JsonResource
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
            'artist_name' => $this->artist_name ?? '-',
            'sound' => Storage::disk('public')->url($this->sound)  ?? '-',
            'image' => Storage::disk('public')->exists($this->image) ? Storage::disk('public')->url($this->image) : null,

        ];

        return $data;
    }
}
