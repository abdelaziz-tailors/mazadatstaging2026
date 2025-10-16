<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {


        if($this->isfollow ){
            $follow=true;
        }else{
            $follow=false;

        }
        if ($this->resource['phone'] == null) {
            $profile_completed=false;
        }else{
            $profile_completed=true;

        }


        $data = [
            'coin'=>$this->user_coin->coin ?? 0,
            'live-video'=> VideoGiftResource::collection($this->user_live_video_gifts)

        ];

        return $data;
    }
}
