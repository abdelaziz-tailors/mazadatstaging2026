<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $guard_name = 'admin';

    public function hasPermissionAt ($permission) {
        return false;
    }


    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');

    }
    public function scopePartner($query) {
        return $query->where('type', 'partner');
    }

    public function getRoleAttribute() {
        if (count(json_decode($this->getRoleNames())) > 0)
        {
            return json_decode($this->getRoleNames())[0];
        }
        else {
            return NULL;
        }
    }
}
