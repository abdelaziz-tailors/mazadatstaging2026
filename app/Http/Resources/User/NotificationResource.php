<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * icon/color hints per notification type, matching the mobile app's
     * notification list design (identity verified = green shield, upcoming
     * auction = gavel, payment reminder = orange dollar sign, etc.)
     */
    private const TYPE_META = [
        'identity_verified' => ['icon' => 'shield-check', 'color' => 'success'],
        'upcoming_auction' => ['icon' => 'gavel', 'color' => 'info'],
        'payment_reminder' => ['icon' => 'dollar', 'color' => 'warning'],
        'auction_won' => ['icon' => 'trophy', 'color' => 'success'],
        'outbid' => ['icon' => 'close', 'color' => 'danger'],
    ];

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $meta = self::TYPE_META[$this->type] ?? ['icon' => 'bell', 'color' => 'default'];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'icon' => $meta['icon'],
            'color' => $meta['color'],
            'title' => $this->title,
            'description' => $this->description,
            'data' => $this->data ?? [],
            'is_read' => ! is_null($this->read_at),
            'time' => $this->created_at?->diffForHumans(),
            'created_at' => $this->created_at,
        ];
    }
}
