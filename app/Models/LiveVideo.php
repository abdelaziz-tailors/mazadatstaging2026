<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveVideo extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function user_Video()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user_auction()
    {
        return $this->belongsTo(User::class, 'user_price_id');
    }
    public function partner()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function partnerData()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->select('id', 'name->'.app()->getLocale().' as name');;
    }

    public function video_items()
    {
        return $this->hasMany(LiveVideoItem::class, 'live_video_id');
    }

    public function user_finished_items()
    {
        return $this->hasMany(LiveVideoItem::class, 'live_video_id')->where('user_finished_id', auth('api')->user()->id);
    }


    public function all_views()
    {
        return $this->hasMany(VideoView::class,'video_id');
    }

    public function likes()
    {
        return $this->hasMany(LiveVideoLike::class,'live_video_id');
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


    public function categoryData()
    {
        return $this->belongsTo(Category::class, 'category_id')->select('id', 'name->'.app()->getLocale().' as name');

    }



    public function gifts()
    {
        return $this->hasMany(UserGift::class, 'video_id');
    }


    public function live_video_user()
    {
        return $this->hasMany(LiveVideoUser::class, 'live_video_id');
    }
    public function leave_live_video_user()
    {
        return $this->hasMany(LiveVideoUser::class, 'live_video_id')->where('leave',1);
    }
    public function in_live_video_user()
    {
        return $this->hasMany(LiveVideoUser::class, 'live_video_id')->where('leave',0);
    }

    public function Live_Video_count()
    {
        return $this->HasMany(LiveVideoLike::class, 'live_video_id');
    }



    public function favorites()
    {
        return $this->hasMany(VideoFavorites::class, 'video_id');
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

 
    public function sub_total()
    {
        return $this->user_finished_items->sum('finished_price');
    }

    public function total_price()
    {
        $tax = $this->sub_total() * ((float) ($this->tax_amount ?? 0)) / 100;

        if ($this->commission_payer == 'buyer') {
            $commission = $this->sub_total() * ((float) ($this->commission_amount ?? 0)) / 100;
            return $this->sub_total() + $tax + $commission;
        }

        return $this->sub_total() + $tax;
    }

}
