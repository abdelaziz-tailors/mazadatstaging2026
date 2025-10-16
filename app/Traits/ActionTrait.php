<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;

trait ActionTrait
{
    public function trait_active_toogler ($item) {
        if ($item->is_active == 0) {$item->is_active = 1;}
        else {$item->is_active = 0;}
        $item->save();
    }

    public function trait_recommended_toogler ($item) {
        if ($item->is_recommended == 0) {$item->is_recommended = 1;}
        else {$item->is_recommended = 0;}
        $item->save();
    }

    public function trait_verified_toogler ($item) {
        if ($item->is_verified == 0) {$item->is_verified = 1;}
        else {$item->is_verified = 0;}
        $item->save();
    }

    public function trait_default_toogler ($item) {
        if ($item->is_default == 0) 
        {
            $item->is_default = 1;
        }
        else {$item->is_default = 0;}
        $item->save();
    }

    
}