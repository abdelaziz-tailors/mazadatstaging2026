<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserNoTokenResource extends JsonResource
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
            'email' => $this->resource['email'] ?? '-',
            // 'profile_completed'=>$profile_completed,
            'phone' => $this->resource['phone'] ?? Null,
            'user_name ' => $this->resource['user_name'] ?? '-',
            'is_verified' => boolval($this->resource['is_verified']),
            'user_type' => $this->resource['user_type'] ?? '-',
            'image' => (Storage::disk('public')->exists($this->image)) ? Storage::disk('public')->url($this->image) : asset('images/logo.png'),
        ];

        if($this->resource['user_type'] == 'vendor'){
            $data['tax_certificate'] = (Storage::disk('public')->exists($this->tax_certificate)) ? Storage::disk('public')->url($this->tax_certificate) : asset('images/logo.png');
            $data['license'] = (Storage::disk('public')->exists($this->license)) ? Storage::disk('public')->url($this->license) : asset('images/logo.png');
            $data['commercial_register'] = (Storage::disk('public')->exists($this->commercial_register)) ? Storage::disk('public')->url($this->commercial_register) : asset('images/logo.png');
        }

        return $data;
    }
}
