<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\Html\Editor\Fields\BelongsTo;

class VideoFavorites extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    public function video()
    {
        return $this->belongsTo(LiveVideo::class, 'video_id','id');
    }

}
