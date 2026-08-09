<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveVideoItem extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with = ['pieces'];
    public function categoryData()
    {
        return $this->belongsTo(Category::class, 'category_id')->select('id', 'name->'.app()->getLocale().' as name');

    }
    public function videoLive()
    {
        return $this->belongsTo(LiveVideo::class, 'live_video_id');

    }

    public function user_auction()
    {
        return $this->belongsTo(User::class, 'user_finished_id');
    }
    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id')->select('id', 'name->'.app()->getLocale().' as name','color');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderItem()
    {
        return $this->hasOne(OrderItem::class, 'live_video_item_id');
    }

    public function order()
    {
        return $this->hasOneThrough(
            Order::class,
            OrderItem::class,
            'live_video_item_id',
            'id',
            'id',
            'order_id'
        );
    }

    public function pieces()
    {
        return $this->hasMany(LiveVideoItemPiece::class)->orderBy('piece_number');
    }

    public function transferredFrom()
    {
        return $this->belongsTo(self::class, 'transferred_from_item_id');
    }

    public function transferOrigin()
    {
        return $this->belongsTo(self::class, 'transfer_origin_item_id');
    }

    public function transferredCopies()
    {
        return $this->hasMany(self::class, 'transferred_from_item_id');
    }

    public function primaryPiece(): ?LiveVideoItemPiece
    {
        if ($this->relationLoaded('pieces')) {
            return $this->pieces->first();
        }

        return $this->pieces()->orderBy('piece_number')->first();
    }

    public function resolvedPieces()
    {
        if ($this->relationLoaded('pieces')) {
            return $this->pieces;
        }

        return $this->pieces()->orderBy('piece_number')->get();
    }
}
