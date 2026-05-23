<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {



            // if ($this->resource['phone'] == null) {
            //     $profile_completed=false;
            // }else{
            //     $profile_completed=true;
            // }
        $shareBaseUrl = rtrim((string) config('deep_links.base_url', config('app.url')), '/');

        $data = [
            'id' => $this->resource['id'] ??'',
            'name' => $this->resource['name'] ?? '-',
            'email' => $this->resource['email'] ?? '-',
            // 'profile_completed'=>$profile_completed,
            'phone' => $this->resource['phone'] ?? Null,
            'user_name' => $this->resource['user_name'] ?? '-',
            'user_name ' => $this->resource['user_name'] ?? '-',
            'share_url' => isset($this->resource['id']) ? ($shareBaseUrl . '/u/' . $this->resource['id']) : null,
            'is_verified' => boolval($this->resource['is_verified']),
            'user_type' => $this->resource['user_type'] ?? '-',
            'image' => (Storage::disk('public')->exists($this->image)) ? Storage::disk('public')->url($this->image) : NULL,
        ];
        if (isset($this->token) && $this->token != NULL) {
            $data['token']  = $this->token;
        }

        if($this->resource['user_type'] == 'vendor'){

            $data['commercial_register'] = (Storage::disk('public')->exists($this->commercial_register)) ? Storage::disk('public')->url($this->commercial_register) : NULL;
            $data['tax_certificate'] = (Storage::disk('public')->exists($this->tax_certificate)) ? Storage::disk('public')->url($this->tax_certificate) : NULL;
            $data['license'] = (Storage::disk('public')->exists($this->license)) ? Storage::disk('public')->url($this->license) : NULL;
            $data['is_main'] =   $this->resource['is_main']==1 ? true : false;
        }

        return $data;
    }
}
