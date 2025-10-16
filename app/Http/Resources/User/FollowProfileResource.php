<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class FollowProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {


        if($this->user_follow->isfollow ){
            $follow=true;
        }else{
            $follow=false;

        }


        $data = [
            'id' => $this->user_follow['id'] ??'',
            'name' => $this->user_follow['name'] ?? '-',
            'email' => $this->user_follow['email'] ?? '-',
            'phone' => $this->user_follow['phone'] ?? '-',
            'user_name ' => $this->user_follow['user_name'] ?? '-',
            'birth_date' => $this->user_follow['birth_date'] ?? '-',
            'bio' => $this->user_follow['bio'] ?? '-',
            'image' => (Storage::disk('public')->exists($this->user_follow->image)) ? Storage::disk('public')->url($this->user_follow->image) : asset('images/logo.png'),
            'videos'=> (optional($this->user_follow->user_Video) != NULL) ? UserVideoResource::collection(optional($this->user_follow->user_Video)) : NULL,
            'favorites_video'=> (optional($this->user_follow->user_Favorites) != NULL) ? UserVideoFavoriteResource::collection(optional($this->user_follow->user_Favorites)) : NULL,
            'follow'=>$follow,
             'follower_count'=>count($this->user_follow->followers),
            'friend_status'=>$this->user_follow->user_friend_received->type ?? $this->user_follow->user_friend_send->type ?? 'none'

        ];

        return $data;
    }
}
