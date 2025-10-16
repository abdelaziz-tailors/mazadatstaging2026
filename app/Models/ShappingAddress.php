<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShappingAddress extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function liveVideoItem()
    {
        return $this->belongsTo(LiveVideoItem::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class)->select('id', 'name->'.app()->getLocale().' as name');
    }
}
