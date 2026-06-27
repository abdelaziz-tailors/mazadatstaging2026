<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'finished_price' => 'float',
        'settled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function liveVideoItem(): BelongsTo
    {
        return $this->belongsTo(LiveVideoItem::class, 'live_video_item_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function services()
    {
        return $this->hasMany(OrderItemService::class);
    }
}
