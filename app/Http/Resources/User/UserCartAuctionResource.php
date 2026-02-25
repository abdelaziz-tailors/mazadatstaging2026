<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCartAuctionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        
       
        $data = [
            'id' => $this->id ??'',
            'title' => $this->title ??'',
            'items_cart'=> CartItemResource::collection($this->user_finished_items),
            'total_price' => $this->user_finished_items->sum('finished_price'),
        ];
        return $data;
    }
}
