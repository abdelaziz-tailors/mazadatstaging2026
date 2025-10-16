<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CitiesResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class ShippingAddressResource extends JsonResource
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
            'address' => $this->address ??'',
            'city_id' => $this->city_id ??'',
            'lat' => $this->lat ??'',
            'lng' => $this->lng ??'',
            'city'=> New CitiesResource($this->city),

        ];

        return $data;
    }
}
