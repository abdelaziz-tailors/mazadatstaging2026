<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserMainDataResource extends JsonResource
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
            'id' => $this->resource['id'] ??'',
            'name' => $this->resource['name'] ?? '-',
            'user_name ' => $this->resource['user_name'] ?? '-',
            'birth_date' => $this->resource['birth_date'] ?? '-',
            'image' => (Storage::disk('public')->exists($this->image)) ? Storage::disk('public')->url($this->image) : asset('images/logo.png'),

        ];

        return $data;
    }
}
