<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CityResource;
use App\Http\Resources\DepartmentResource;
use App\Models\JobTitle;
use App\Models\LiveVideo;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use TranslationHelper;

class PartnerResource extends JsonResource
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
            'name' => $this->resource['name'] ?? '-',
            'email' => $this->resource['email'] ?? '-',
            'phone' => $this->resource['phone'] ?? Null,
            'user_name ' => $this->resource['user_name'] ?? '-',
            'image' => (!empty($this->image) && Storage::disk('public')->exists($this->image))
                ? Storage::disk('public')->url($this->image)
                : asset('images/logo.png'),
            'is_verified' => boolval($this->resource['is_verified']),
            'active_auctions_count' => $this->activeAuctionsCount(),

        ];
        return $data;
    }

    /**
     * Number of this partner's own auctions that are currently live
     * (status = 'start'), computed fresh on every request — same
     * "auctions they organize" semantics as BalanceResource's
     * active_bids_count for a vendor account (LiveVideo.user_id).
     */
    private function activeAuctionsCount(): int
    {
        return LiveVideo::where('status', 'start')
            ->where('user_id', $this->resource['id'] ?? null)
            ->count();
    }
}
