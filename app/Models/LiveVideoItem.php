<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveVideoItem extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function categoryData()
    {
        return $this->belongsTo(Category::class, 'category_id')->select('id', 'name->'.app()->getLocale().' as name');

    }
    public function age_avlue()
    {
        return $this->belongsTo(Age::class, 'age_id')->select('id', 'name->'.app()->getLocale().' as name');

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
    public function addressData()
    {
        return $this->hasOne(ShappingAddress::class,'live_video_item_id');
    }

}
