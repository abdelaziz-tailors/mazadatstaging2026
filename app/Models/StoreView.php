<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreView extends Model
{
    use HasFactory;
    protected  $guarded=['id'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

}
