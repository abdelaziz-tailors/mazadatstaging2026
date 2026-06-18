<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class CartItemResource extends JsonResource
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
            'title' => $this->title ??'',
            'cart_status'=>$this->status_cart ??'pending',
            'status'=>$this['status'],
            'image'=>$files,
            'end_at'=>$this['end_at'],
            'category'=>New CategoryResource($this->categoryData),
            'information'=>$this['information'],
            'weight'=>$this['weight'],
            'age'=>$this['age'],
            'quantity'=>$this['quantity'],
            'start_price'=>$this->videoLive->start_price ?? $this['start_price'],
            'bidding'=>$this['bidding'],
            'finished_price'=>$this['finished_price'],
            'user'=> New UserDataResource($this->videoLive->user_Video),
            'user_take_auction'=> New UserDataResource($this->user_auction),
            // 'address'=> New ShippingAddressResource($this->addressData ?? ''),         
        ];

        return $data;
    }
}
