<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use App\Models\Store;
use App\Models\User\User;
use App\Models\VideoLike;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class AllStoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        if(!isset($this->user_live_video->id)){
            $live_video=[
                'status'=>false,
                'live_id'=>null,

            ];
        }else{
            $live_video=[
                'status'=>true,
                'live_id'=>$this->user_live_video->id,

            ];
        }
        if($this->isfollow ){
            $follow=true;
        }else{
            $follow=false;

        }



        $today = Carbon::today();
        $Store=Store::where('user_id',$this->resource['id'])
                    ->whereDate('start_at', '<=', $today)
                    ->whereDate('end_at', '>=', $today)
            ->orderBy('id', 'desc')->get();

        $data = [
            'id' => $this->resource['id'] ??'',
            'name' => $this->resource['name'] ?? '-',
            'email' => $this->resource['email'] ?? '-',
            'phone' => $this->resource['phone'] ?? '-',
            'user_name ' => $this->resource['user_name'] ?? '-',
            'birth_date' => $this->resource['birth_date'] ?? '-',
            'bio' => $this->resource['bio'] ?? '-',
            'live_video'=>$live_video ??'',
            'image' => (Storage::disk('public')->exists($this->image)) ? Storage::disk('public')->url($this->image) : asset('images/logo.png'),
            'follow'=>$follow,
            'follower_count'=>count($this->followers),
//            'follower'=>ProfileResource::collection($this->followers)
            'friend_status'=>$this->user_friend_received->type ?? $this->user_friend_send->type ?? 'none',
            'story'=>UserStoryResource::collection($Store)


        ];

        return $data;
    }
}
