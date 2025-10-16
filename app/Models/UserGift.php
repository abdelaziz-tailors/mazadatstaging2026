<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGift extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    public function gift_data()
    {
        return $this->belongsTo(Gift::class, 'gift_id')->select('id', 'name->'.app()->getLocale().' as name', 'coin', 'image_svg', 'image_png');
    }
    public function user_gift()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
