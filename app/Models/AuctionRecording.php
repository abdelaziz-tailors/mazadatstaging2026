<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionRecording extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['resource_id', 'sid', 'channel', 'uid', 'prefix', 'last_error'];
    protected $casts = [
        'acquired_at' => 'datetime', 'completed_at' => 'datetime',
        'expires_at' => 'datetime', 'retry_at' => 'datetime',
    ];

    public function auction()
    {
        return $this->belongsTo(LiveVideo::class, 'live_video_id');
    }

    public function replayAvailable(): bool
    {
        return $this->status === 'ready' && $this->expires_at && $this->expires_at->isFuture();
    }
}
