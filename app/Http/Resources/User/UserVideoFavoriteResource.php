<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CitiesResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use App\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserVideoFavoriteResource extends JsonResource
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

         if( !empty($this->video->file) && is_array(json_decode($this->video->file))){
             $index = 0;
             foreach (json_decode($this->video->file) as $feature) {
                 $index++;
                 $files[] = [
                     'key' => $index,
                     'file' => Storage::disk('public')->url($feature) ,
                 ];
             }
         }else{
             $files[] = [
                 'key' => 1,
                 'file' => Storage::disk('public')->url($this->video->file) ,
             ];
         }


        $is_like=count($this->video->islike);
        if($is_like > 0){
            $like=true;
        }else{
            $like=false;

        }
        $is_favorites=count($this->video->isfavorite);
        if($is_favorites > 0){
            $favorites=true;
        }else{
            $favorites=false;
        }
//

        $mention = [];
        if( !empty($this->video->mention) && is_array(json_decode($this->video->mention))){
            foreach (json_decode($this->video->mention) as $mention_user) {

                $user=User::find($mention_user);
                if ($user){
                    $mention[] =new UserDataResource($user) ;

                }
            }
        }






        $data = [
            'id' => $this->video->id ??'',
            'title'=>app()->getLocale()=='en'? $this->video['title']??'' :$this->video['title_ar']??'',
            'title_en' => $this->video->title ??'',
            'title_ar' => $this->video->title_ar ??'',
            'status'=>$this->video['status'],
            'image'=>$files,
            'date_start_at'=>$this->video['date_start_at'],
            'date_end_at'=>$this->video['date_end_at'],
            'time_start_at'=>$this->video['time_start_at'],
            'time_end_at'=>$this->video['time_end_at'],
            'information'=>app()->getLocale()=='en'? $this->video['information']??'':$this->video['information_ar']??'',
            'information_en' => $this->video->information ??'',
            'information_ar' => $this->video->information_ar ??'',
            'terms_conditions'=>app()->getLocale()=='en'? $this->video['terms_conditions']??'':$this->video['terms_conditions_ar']??'',
            'terms_conditions_en' => $this->video->terms_conditions ??'',
            'terms_conditions_ar' => $this->video->terms_conditions_ar ??'',
            'views_count'=>count($this->video->all_views),
            'video_type'=>$this->video['type'],
            'is_favorites'=>$favorites,

            'partners_type'=>$this->video['partners_type'],
            'partner'=> New PartnerResource($this->video->partnerData),
            'city'=> New CitiesResource($this->video->city),
            'video_items'=> VideoItemResource::collection($this->video->video_items),
            'user'=> New UserDataResource($this->video->user_Video),

        ];

        return $data;
    }
}
