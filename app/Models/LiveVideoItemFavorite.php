<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveVideoItemFavorite extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(LiveVideoItem::class, 'live_video_item_id');
    }
}
