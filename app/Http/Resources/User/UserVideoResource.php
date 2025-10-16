<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use App\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserVideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

         $files = [];
         if( !empty($this->file) && is_array(json_decode($this->file))){
             $index = 0;
             foreach (json_decode($this->file) as $feature) {
                 $index++;
                 $files[] = [
                     'key' => $index,
                     'file' => Storage::disk('public')->url($feature) ,
                 ];
             }
         }else{
             $files[] = [
                 'key' => 1,
                 'file' => Storage::disk('public')->url($this->file) ,
             ];
         }


        $is_like=count($this->islike);
        if($is_like > 0){
            $like=true;
        }else{
            $like=false;

        }
        $is_favorites=count($this->isfavorite);
        if($is_favorites > 0){
            $favorites=true;
        }else{
            $favorites=false;
        }

        if($this->comment_permissions !=1){
            $comment_permissions=true;
        }else{
            $comment_permissions=false;

        }



        $mention = [];
        if( !empty($this->mention) && is_array(json_decode($this->mention))){
            foreach (json_decode($this->mention) as $mention_user) {

                $user=User::find($mention_user);
                if ($user){
                    $mention[] =new UserDataResource($user) ;

                }
            }
        }



        $data = [
            'id' => $this->id ??'',
            'title' => $this->title ?? '-',
            'type' => $this->type ?? '-',
            'lat' => $this->lat ?? '-',
            'lng' => $this->lng ?? '-',
            'view_count'=>count($this->all_views),
            'share_count'=>count($this->all_shares),
            'view_permissions' => $this->view_permissions ??  null,
            'add_comment'=>$comment_permissions,

            'file' => $files,
            'sound' => Storage::disk('public')->exists($this->sound) ? Storage::disk('public')->url($this->sound) : null,
            'like_count'=>count($this->likes),
            'islike'=> $like,
            'favorites_count'=>count($this->favorites),
            'isfavorites'=> $favorites,
            'hashtag' => HashTagsResource::collection($this->hashtags),
            'mention'=>$mention,

        ];

        return $data;
    }
}
