<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class FriendProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {


        if($this->user_friend->isfollow ){
            $follow=true;
        }else{
            $follow=false;

        }


        $data = [
            'id' => $this->user_friend['id'] ??'',
            'name' => $this->user_friend['name'] ?? '-',
            'email' => $this->user_friend['email'] ?? '-',
            'phone' => $this->user_friend['phone'] ?? '-',
            'user_name ' => $this->user_friend['user_name'] ?? '-',
            'birth_date' => $this->user_friend['birth_date'] ?? '-',
            'bio' => $this->user_friend['bio'] ?? '-',
            'image' => (Storage::disk('public')->exists($this->user_friend->image)) ? Storage::disk('public')->url($this->user_friend->image) : asset('images/logo.png'),
            'videos'=> (optional($this->user_friend->user_Video) != NULL) ? UserVideoResource::collection(optional($this->user_friend->user_Video)) : NULL,
            'favorites_video'=> (optional($this->user_friend->user_Favorites) != NULL) ? UserVideoFavoriteResource::collection(optional($this->user_friend->user_Favorites)) : NULL,
            'follow'=>$follow,
             'follower_count'=>count($this->user_friend->followers),
            'friend_status'=>$this->user_friend->user_friend_received->type ?? $this->user_friend->user_friend_send->type ?? 'none'

        ];

        return $data;
    }
}
