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
            'sub_total' => $this->sub_total(),
            'tax' => $this->tax_value(),
            'tax_amount' => $this->tax_amount .'%',
            'commission' => $this->commission_value(),
             $this->mergeWhen($this->commission_payer == 'buyer', function(){
                return [
                    'commission_amount' => $this->commission_amount .'%',
                ];
             }),
            'total_price' => $this->total_price(),
        ];
        return $data;
    }
}
