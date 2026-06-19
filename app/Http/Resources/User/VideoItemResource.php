<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class VideoItemResource extends JsonResource
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
            'lineage_title'=>app()->getLocale()=='en'? $this['title']??'' :$this['title_ar']??'',
            'lineage_title_en' => $this->title ??'',
            'lineage_title_ar' => $this->title_ar ??'',
            'status'=>$this['status'],
            'image'=>$files,
            'end_at'=>$this['end_at'],
            'category'=>New CategoryResource($this->categoryData),
            'information'=>app()->getLocale()=='en'? $this['information']??'' :$this['information_ar']??'',
            'information_en' => $this->information ??'',
            'information_ar' => $this->information_ar ??'',
            'terms'=>app()->getLocale()=='en'? $this['terms']??'' :$this['terms_ar']??'',
            'terms_en' => $this->terms ??'',
            'terms_ar' => $this->terms_ar ??'',
            'address'=>$this['address'],
            'color'=> New ColorResource($this->color),

            'age'=>$this->primaryPiece()?->age,
            'age_type'=>$this->primaryPiece()?->age_type,
            'type'=>$this['type'],
            'weight'=>$this->primaryPiece()?->weight,
            'start_price'=>$this->videoLive->start_price ?? $this['start_price'],
            'bidding'=>$this['bidding'],
            'finished_price'=>$this['finished_price'],
            'user_take_auction'=> New UserDataResource($this->user_auction),
            'health_certificate' => Storage::disk('public')->exists($this->health_certificate) ? Storage::disk('public')->url($this->health_certificate) : null,
            'video'=>Storage::disk('public')->exists($this->video) ? Storage::disk('public')->url($this->video) : null,
            'quantity'=>$this['quantity'],
            'piece_multiplier_number' => $this->piece_multiplier_number ?? '',
            'identifier' => $this->primaryPiece()?->identifier ?? '',
            'baham_count' => $this->primaryPiece()?->baham_count ?? '',
            'pieces' => VideoItemPieceResource::collection($this->resolvedPieces()),
            'user'=> New UserDataResource($this->user),
            'seller_id' => $this->seller_id,
            'seller' => $this->when($this->seller_id, new UserDataResource($this->seller)),
            'show_partner_name'=>$this['show_partner_name'],

        ];

        return $data;
    }
}
