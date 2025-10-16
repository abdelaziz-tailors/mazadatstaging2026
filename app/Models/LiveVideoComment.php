<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveVideoComment extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function user_Video()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function live_video_item()
    {
        return $this->belongsTo(LiveVideoItem::class, 'live_video_item_id');
    }
    public function live_video()
    {
        return $this->belongsTo(LiveVideo::class, 'live_video_id');
    }

}
