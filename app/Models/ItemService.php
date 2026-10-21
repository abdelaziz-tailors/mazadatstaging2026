<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemService extends Model
{
    use SoftDeletes;

    public const PRICING_METHODS = ['fixed', 'per_animal', 'per_animal_per_day'];

    protected $table = 'item_services';

    protected $guarded = ['id'];

    // Catalog configuration is Dashboard-only; keep serialized mobile contracts unchanged.
    protected $hidden = ['cost_pricing_method'];

    protected $casts = [
        'default_price' => 'float',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function pricingMethod(): string
    {
        $method = $this->getAttribute('cost_pricing_method');

        return in_array($method, self::PRICING_METHODS, true) ? $method : 'fixed';
    }

    /**
     * Global default services + services owned by the requested partner.
     */
    public function scopeAvailableForAdmin(Builder $query, ?int $adminId): Builder
    {
        return $query->where(function (Builder $q) use ($adminId) {
            $q->where('is_default', true);

            if ($adminId) {
                $q->orWhere('admin_id', $adminId);
            }
        });
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
