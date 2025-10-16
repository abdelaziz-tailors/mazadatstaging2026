<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserInvoiceItemResource extends JsonResource
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
            'id' => $this->resource['id'] ??'',
            'invoice_id'=>$this->resource['live_video_id'] ."-" . $this->resource['id'],
            'title'=>app()->getLocale()=='en'? $this['title']??'' :$this['title_ar']??'',
            'title_en' => $this->title ??'',
            'title_ar' => $this->title_ar ??'',
            'status'=>$this['status_cart'],
            'date'=>$this['end_at'],
            'price'=>$this->finished_price,
        ];

        return $data;
    }
}
