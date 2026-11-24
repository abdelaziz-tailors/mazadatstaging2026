<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOtp extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['registration_challenge_hash', 'credential_group_id'];

    protected $casts = [
        'expire_at' => 'datetime',
        'is_verified' => 'boolean',
    ];
}
