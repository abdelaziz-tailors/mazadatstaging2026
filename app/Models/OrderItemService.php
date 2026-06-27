<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemService extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'float',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function itemService(): BelongsTo
    {
        return $this->belongsTo(ItemService::class, 'item_service_id');
    }

    public function displayName(?string $locale = null): string
    {
        if ($this->itemService) {
            return $this->itemService->localizedName($locale);
        }

        $locale = $locale ?? app()->getLocale();
        $decoded = json_decode($this->custom_name, true);

        if (is_array($decoded)) {
            return $decoded[$locale] ?? $decoded['ar'] ?? $decoded['en'] ?? '';
        }

        return (string) ($this->custom_name ?? '');
    }
}
