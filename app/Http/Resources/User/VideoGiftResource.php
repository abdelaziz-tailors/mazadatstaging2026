<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\GiftResource;
use App\Http\Resources\PackageResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class VideoGiftResource extends JsonResource
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
            'coin' => $this->gift_coin ,
            'gift_data' => new GiftResource($this->gift_data),
            'user'=> New UserDataResource($this->user_gift),
        ];

        return $data;
    }
}
