<?php

namespace App\Services;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;

class OrderService
{
    /**
     * Attach or refresh a won auction line on the buyer's order for that live stream.
     */
    public static function attachWonItem(LiveVideoItem $item): ?Order
    {
        if (! $item->user_finished_id || ! $item->live_video_id) {
            return null;
        }

        $item->loadMissing('videoLive');
        $live = $item->videoLive;

        if (! $live) {
            return null;
        }

        $order = Order::query()->firstOrCreate(
            [
                'live_video_id' => $item->live_video_id,
                'buyer_id' => (int) $item->user_finished_id,
            ],
            [
                'order_number' => self::generateOrderNumber(),
                'tax_percent' => (float) ($live->tax_amount ?? 0),
                'commission_percent' => (float) ($live->commission_amount ?? 0),
                'commission_payer' => $live->commission_payer ?? 'buyer',
                'service_fee_per_item' => (float) ($live->service_fee ?? 0),
                'payment_status' => 'unpaid',
                'status' => 'pending',
            ],
        );

        self::ensureOrderNumber($order);

        OrderItem::query()->updateOrCreate(
            ['live_video_item_id' => $item->id],
            [
                'order_id' => $order->id,
                'finished_price' => (float) ($item->finished_price ?? 0),
                'seller_id' => $item->seller_id == null ? $item->user_id : $item->seller_id,
            ]
        );

        self::recalculateTotals($order->fresh(['items']));

        return $order->fresh(['items', 'liveVideo', 'buyer']);
    }

    public static function recalculateTotals(Order $order): void
    {
        $order->loadMissing('items');
        $itemCount = $order->items->count();
        $subtotal = (float) $order->items->sum('finished_price');

        $taxValue = round($subtotal * ((float) $order->tax_percent) / 100, 2);
        $commissionValue = ($order->commission_payer === 'buyer')
            ? round($subtotal * ((float) $order->commission_percent) / 100, 2)
            : 0.0;
        $serviceFeeTotal = round(((float) $order->service_fee_per_item) * $itemCount, 2);
        $total = round($subtotal + $taxValue + $commissionValue, 2);

        $order->update([
            'subtotal' => $subtotal,
            'tax_value' => $taxValue,
            'commission_value' => $commissionValue,
            'service_fee_total' => $serviceFeeTotal,
            'total' => $total,
        ]);
    }

    public static function updateOrderStatus(Order $order, string $paymentStatus, string $status): Order
    {
        if ($paymentStatus === 'paid' && $status === 'pending') {
            $status = 'confirmed';
        }

        $order->update([
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        return $order->fresh(['items', 'liveVideo']);
    }

    public static function applyPaymentProof(Order $order, string $relativePath): Order
    {
        $order->update(['payment_proof' => $relativePath]);

        return $order->fresh(['items']);
    }

    public static function applyShippingAddress(Order $order, array $addressData): Order
    {
        $order->update([
            'shipping_address' => $addressData['address'] ?? null,
            'shipping_city_id' => $addressData['city_id'] ?? null,
            'shipping_lat' => $addressData['lat'] ?? null,
            'shipping_lng' => $addressData['lng'] ?? null,
        ]);

        return $order->fresh(['shippingCity']);
    }

    public static function resolveForItem(LiveVideoItem $item): ?Order
    {
        $order = $item->order;

        if ($order) {
            return $order;
        }

        return self::attachWonItem($item);
    }

    public static function ensureOrderNumber(Order $order): Order
    {
        if ($order->order_number) {
            return $order;
        }

        $order->update([
            'order_number' => self::generateOrderNumber($order->created_at),
        ]);

        return $order->fresh();
    }

    public static function generateOrderNumber(?\DateTimeInterface $at = null): string
    {
        $at = $at ? \Illuminate\Support\Carbon::parse($at) : now();
        $prefix = 'ORD-'.$at->format('Ymd').'-';

        $last = Order::withTrashed()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
