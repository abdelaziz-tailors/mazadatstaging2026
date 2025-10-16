<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded=['id'];
    public function user_Video()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(VideoFavorites::class);
    }
    public function comments()
    {
        return $this->hasMany(VideoComment::class)->whereNull('comment_id');
    }
    public function all_comments()
    {
        return $this->hasMany(VideoComment::class);
    }
    public function all_views()
    {
        return $this->hasMany(VideoView::class);
    }
    public function all_shares()
    {
        return $this->hasMany(VideoShare::class);
    }

    public function isfavorite()
    {
        // Check if there's a favorite record associated with this post and the current user
        if(isset(auth('api')->user()->id)){
            return $this->favorites()->where('user_id', auth('api')->user()->id);

        }else{
            return $this->favorites()->where('user_id', '');
        }
    }

    public function likes()
    {
        return $this->hasMany(VideoLike::class);
    }

    public function islike()
    {
        // Check if there's a favorite record associated with this post and the current user
        if(isset(auth('api')->user()->id)){
            return $this->likes()->where('user_id', auth('api')->user()->id);

        }else{
            return $this->likes()->where('user_id', '');
        }
    }
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class);
    }




}
