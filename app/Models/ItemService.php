<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemService extends Model
{
    use SoftDeletes;

    protected $table = 'item_services';

    protected $guarded = ['id'];

    protected $casts = [
        'default_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $decoded = json_decode($this->name, true);

        if (is_array($decoded)) {
            return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? '';
        }

        return (string) $this->name;
    }
}
