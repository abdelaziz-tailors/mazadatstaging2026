<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoComment extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    public function user_Video()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function replay_comment()
    {
        return $this->hasMany(VideoComment::class,'comment_id');
    }
    public function comment_like()
    {
        return $this->hasMany(VideoCommentLike::class,'comment_id');
    }

}
