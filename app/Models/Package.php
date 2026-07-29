<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'package_id');
    }

    /**
     * Localized feature bullet list, stored as {"ar": [...], "en": [...]}
     * (same shape/pattern as name/description). Empty array if unset.
     */
    public function featuresList(?string $locale = null): array
    {
        $decoded = json_decode($this->features ?? '', true);

        return $decoded[$locale ?? app()->getLocale()] ?? [];
    }
}
