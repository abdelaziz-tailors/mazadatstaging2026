<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserLiveVideoResource extends JsonResource
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
            'start_at'=>$this['created_at']->format('d-m-Y H:i:s'),
            'like_count'=>count($this->Live_Video_count),
            'user'=> New UserDataResource($this->user_Video)

        ];

        return $data;
    }
}
