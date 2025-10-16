<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;

trait AuthorizeTrait
{
    public function authorizable ($permission) {
        if(Auth::guard('admin')->user()->can($permission)) {
            return true;
        }
        else {
            abort('403', 'THIS ACTION IS UNAUTHORIZED.');
        }
    }
}