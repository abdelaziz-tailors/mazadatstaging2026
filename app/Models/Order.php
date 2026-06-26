<?php

namespace App\Models;

use App\Models\User\User;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'float',
        'tax_percent' => 'float',
        'tax_value' => 'float',
        'commission_percent' => 'float',
        'commission_value' => 'float',
        'service_fee_per_item' => 'float',
        'service_fee_total' => 'float',
        'total' => 'float',
        'settled_at' => 'datetime',
    ];

    public function liveVideo(): BelongsTo
    {
        return $this->belongsTo(LiveVideo::class, 'live_video_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function shippingCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'shipping_city_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function invoiceId(): string
    {
        return $this->order_number ?? (string) $this->id;
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = OrderService::generateOrderNumber();
            }
        });
    }
}
