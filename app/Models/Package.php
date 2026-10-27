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
        $features = \App\Helpers\TranslationHelper::localizedValue($this->features, $locale);

        return array_values(array_filter(is_array($features) ? $features : [$features], function ($feature) {
            return is_string($feature) && trim($feature) !== '';
        }));
    }
}
