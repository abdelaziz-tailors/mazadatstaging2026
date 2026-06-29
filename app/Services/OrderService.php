<?php

namespace App\Services;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Attach or refresh a won auction line on the buyer's order for that live stream.
     */
    public static function activeCartOrdersQuery(int $buyerId, ?int $orderId = null): Builder
    {
        $query = Order::query()
            ->where('buyer_id', $buyerId)
            ->activeCart();

        if ($orderId !== null) {
            $query->whereKey($orderId);
        }

        return $query;
    }

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
        $order->loadMissing('items.services');
        $itemCount = $order->items->count();
        $subtotal = (float) $order->items->sum('finished_price');
        $pieceServicesTotal = PieceServiceService::sumItemServicesForOrder($order);

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
            'piece_services_total' => $pieceServicesTotal,
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

    /**
     * Per-consignor seller invoice breakdown for items in this order (seller_id on the lot).
     */
    public static function sellerInvoiceSummariesForOrder(Order $order, ?int $onlySellerId = null): Collection
    {
        $order->loadMissing(['liveVideo', 'items.liveVideoItem.seller', 'items.liveVideoItem.pieces', 'items.services']);

        $live = $order->liveVideo;
        $commissionPayer = $live?->commission_payer ?? 'buyer';
        $commissionPct = (float) ($live?->commission_amount ?? 0);
        $serviceFeePerItem = (float) ($live?->service_fee ?? 0);

        $sellerItems = $order->items->filter(function (OrderItem $orderItem) use ($onlySellerId) {
            $sellerId = $orderItem->liveVideoItem?->seller_id;
            if (! $sellerId) {
                return false;
            }

            return $onlySellerId === null || (int) $sellerId === (int) $onlySellerId;
        });

        return $sellerItems
            ->groupBy(fn (OrderItem $orderItem) => (int) $orderItem->liveVideoItem->seller_id)
            ->map(function (Collection $orderItems, int|string $sellerId) use ($commissionPayer, $commissionPct, $serviceFeePerItem) {
                $sellerId = (int) $sellerId;
                $seller = $orderItems->first()->liveVideoItem->seller;

                $lines = [];
                $gross = 0.0;
                $commissionTotal = 0.0;
                $serviceTotal = 0.0;
                $pieceServicesTotal = 0.0;
                $net = 0.0;

                foreach ($orderItems as $orderItem) {
                    $item = $orderItem->liveVideoItem;
                    $finished = (float) $orderItem->finished_price;
                    $pieceServices = PieceServiceService::sumItemServicesForOrderItem($orderItem);
                    $commission = $commissionPayer === 'seller'
                        ? round($commissionPct * $finished / 100, 2)
                        : 0.0;
                    $serviceFee = $serviceFeePerItem;
                    $lineNet = round($finished - $commission - $serviceFee - $pieceServices, 2);

                    $title = app()->getLocale() === 'ar'
                        ? ($item->title_ar ?? $item->title ?? '—')
                        : ($item->title ?? $item->title_ar ?? '—');

                    $lines[] = [
                        'order_item_id' => $orderItem->id,
                        'live_video_item_id' => $item?->id,
                        'title' => $title,
                        'price' => $finished,
                        'commission' => $commission,
                        'service_fee' => $serviceFee,
                        'piece_services' => $pieceServices,
                        'net' => $lineNet,
                        'pieces' => $item
                            ? $item->resolvedPieces()->map(fn ($piece) => [
                                'id' => $piece->id,
                                'piece_number' => $piece->piece_number,
                                'age' => $piece->age,
                                'weight' => $piece->weight,
                                'identifier' => $piece->identifier ?? '',
                                'baham_count' => $piece->baham_count ?? '',
                            ])->values()->all()
                            : [],
                    ];

                    $gross += $finished;
                    $commissionTotal += $commission;
                    $serviceTotal += $serviceFee;
                    $pieceServicesTotal += $pieceServices;
                    $net += $lineNet;
                }

                return [
                    'seller_id' => $sellerId,
                    'seller_name' => $seller->name ?? '—',
                    'lines' => $lines,
                    'gross' => round($gross, 2),
                    'commission' => round($commissionTotal, 2),
                    'service_fee' => round($serviceTotal, 2),
                    'piece_services' => round($pieceServicesTotal, 2),
                    'net' => round($net, 2),
                ];
            })
            ->values();
    }
}
