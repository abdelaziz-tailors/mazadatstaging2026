<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class ProfileResource extends JsonResource
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
            'id' => $this->resource['id'] ??'',
            'name' => $this->resource['name'] ?? '-',
            'email' => $this->resource['email'] ?? '-',
            // 'profile_completed'=>$profile_completed,
            'phone' => $this->resource['phone'] ?? Null,
            'user_name ' => $this->resource['user_name'] ?? '-',
            'is_verified' => boolval($this->resource['is_verified']),
            'user_type' => $this->resource['user_type'] ?? '-',
            'image' => (Storage::disk('public')->exists($this->image)) ? Storage::disk('public')->url($this->image) : NULL,
            // 'videos'=> (optional($this->user_Video) != NULL) ? UserVideoResource::collection(optional($this->user_Video)) : NULL,
            'favorites_video'=> (optional($this->user_Favorites) != NULL) ? UserVideoFavoriteResource::collection(optional($this->user_Favorites)) : NULL,
            'follow'=>$follow,
            'follower_count'=>count($this->followers),
            'auction_win_videos'=> (optional($this->wonAuctionItemsWithVideos) != null) ? AuctionWinVideoResource::collection($this->wonAuctionItemsWithVideos) : [],
//            'follower'=>ProfileResource::collection($this->followers)
            // 'friend_status'=>$this->user_friend_received->type ?? $this->user_friend_send->type ?? null,
            // 'coin'=>$this->user_coin->coin ?? 0,

        ];
        if($this->resource['user_type'] == 'vendor'){

            $data['commercial_register'] = (Storage::disk('public')->exists($this->commercial_register)) ? Storage::disk('public')->url($this->commercial_register) : NULL;
            $data['tax_certificate'] = (Storage::disk('public')->exists($this->tax_certificate)) ? Storage::disk('public')->url($this->tax_certificate) : NULL;
            $data['license'] = (Storage::disk('public')->exists($this->license)) ? Storage::disk('public')->url($this->license) : NULL;
            $data['is_main'] =   $this->resource['is_main']==1 ? true : false;
        }

        return $data;
    }
}
