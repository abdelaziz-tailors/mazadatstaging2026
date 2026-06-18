<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CitiesResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class MyLiveVideoResource extends JsonResource
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
        if( !empty($this->image) && is_array(json_decode($this->image))){
            $index = 0;
            foreach (json_decode($this->image) as $feature) {
                $index++;
                $files[] = [
                    'key' => $index,
                    'file' => Storage::disk('public')->url($feature) ,
                ];
            }
        }else{
            $files[] = [
                'key' => 1,
                'file' => Storage::disk('public')->url($this->image) ,
            ];
        }


        $data = [
            'id' => $this->id ??'',
            'title'=>app()->getLocale()=='en'? $this['title']??'' :$this['title_ar']??'',
            'title_en' => $this->title ??'',
            'title_ar' => $this->title_ar ??'',
            'status'=>$this['status'],
            'color'=>$this['color'],
            'image'=>$files,
            'date_start_at'=>$this['date_start_at'],
            'date_end_at'=>$this['date_end_at'],
            'time_start_at'=>$this['time_start_at'],
            'time_end_at'=>$this['time_end_at'],
            'start_price' => $this->start_price,
            'information'=>app()->getLocale()=='en'? $this['information']??'' :$this['information_ar']??'',
            'information_en' => $this->information ??'',
            'information_ar' => $this->information_ar ??'',
            'terms_conditions'=>app()->getLocale()=='en'? $this['terms_conditions']??'' :$this['terms_conditions_ar']??'',
            'terms_conditions_en' => $this->terms_conditions ??'',
            'terms_conditions_ar' => $this->terms_conditions_ar ??'',
            'city'=> New CitiesResource($this->city),
            'views_count'=>count($this->all_views),
            'video_type'=>$this['type'],
            'partners_type'=>$this['partners_type'],
            'partner'=> New PartnerResource($this->partnerData),
            'video_items'=> VideoItemResource::collection($this->video_items),
            'user'=> New UserDataResource($this->user_Video),

            // Agora Live Streaming Configuration
            'agora' => [
                'app_id' => $this->agora_app_id ?? null,
                'channel_name' => $this->agora_channel_name ?? null,
                'token' => $this->agora_token ?? null,
                'uid' => $this->user_id ?? 0,

            ],
            'auction_fees' => [
                'tax_amount' => isset($this->tax_amount) && $this->tax_amount !== null ?  $this->tax_amount : null,
                'commission_amount' => isset($this->commission_amount) && $this->commission_amount !== null ?  $this->commission_amount : null,
                'commission_payer' => $this->commission_payer,
                'service_fee' => isset($this->service_fee) && $this->service_fee !== null ?  $this->service_fee : null,
            ],

        ];

        return $data;
    }
}
