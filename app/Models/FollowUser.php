<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUser extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    public function user_follow()
    {
        return $this->belongsTo(User::class, 'follow_id');
    }


}
