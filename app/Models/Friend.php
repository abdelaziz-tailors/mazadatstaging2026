<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Friend extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    public function user_follow()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
    public function user_friend()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
