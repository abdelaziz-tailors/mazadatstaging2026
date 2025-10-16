<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use App\Models\User\User;
use App\Models\VideoLike;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class UserStoryResource extends JsonResource
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
            'type' => $this->type ?? '-',
            'start_at' => $this->start_at ?? '-',
            'end_at' => $this->end_at ?? '-',
            'view_count'=>count($this->all_views),
            'file' => Storage::disk('public')->url($this->file) ,
            'sound' => Storage::disk('public')->exists($this->sound) ? Storage::disk('public')->url($this->sound) : null,
            'view'=>  UserViewStoryResource::collection($this->all_views),
        ];

        return $data;
    }
}
