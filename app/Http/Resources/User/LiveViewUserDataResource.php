<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class LiveViewUserDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {



        if(!isset($this->user->user_live_video->id)){
            $live_video=[
                'status'=>false,
                'live_id'=>null,

            ];
        }else{
            $live_video=[
                'status'=>true,
                'live_id'=>$this->user->user_live_video->id,

            ];
        }
        if($this->user->isfollow ){
            $follow=true;
        }else{
            $follow=false;

        }


        $data = [
            'id' => $this->user->id ??'',
            'name' => $this->user->name ?? '-',
            'email' => $this->user['email'] ?? '-',
            'phone' => $this->user['phone'] ?? '-',
            'user_name ' => $this->user['user_name'] ?? '-',
            'birth_date' => $this->user['birth_date'] ?? '-',
            'bio' => $this->user['bio'] ?? '-',
            'live_video'=>$live_video ??'',
            'image' => (Storage::disk('public')->exists($this->user->image)) ? Storage::disk('public')->url($this->user->image) : asset('images/logo.png'),
            'follow'=>$follow,
            'follower_count'=>count($this->user->followers),
//            'follower'=>ProfileResource::collection($this->user->followers)
            'friend_status'=>$this->user->user_friend_received->type ?? $this->user->user_friend_send->type ?? null

        ];

        return $data;
    }
}
