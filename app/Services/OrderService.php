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
        $order->loadMissing(['liveVideo', 'items.seller', 'items.liveVideoItem.pieces', 'items.services']);

        $live = $order->liveVideo;
        $commissionPayer = $live?->commission_payer ?? 'buyer';
        $commissionPct = (float) ($live?->commission_amount ?? 0);
        $serviceFeePerItem = (float) ($live?->service_fee ?? 0);

        // OrderItem.seller_id (not LiveVideoItem.seller_id) is the
        // authoritative, already-resolved seller for this sale —
        // attachWonItem() sets it to the item's own seller_id, or falls back
        // to the item's user_id (the organizer) when the piece is the
        // organizer's own and was never given an explicit seller_id. Using
        // liveVideoItem->seller_id here instead silently dropped the
        // organizer's own pieces whenever that fallback was the only place
        // seller_id ever got resolved.
        //
        // Exception: if LiveVideoItem.seller_id IS explicitly set but
        // disagrees with OrderItem.seller_id, that's a genuine data
        // anomaly, not an own-piece fallback — excluded, same as before.
        $sellerItems = $order->items->filter(function (OrderItem $orderItem) use ($onlySellerId) {
            $sellerId = $orderItem->seller_id;
            if (! $sellerId) {
                return false;
            }

            $liveVideoItemSellerId = $orderItem->liveVideoItem?->seller_id;
            if ($liveVideoItemSellerId !== null && (int) $liveVideoItemSellerId !== (int) $sellerId) {
                return false;
            }

            return $onlySellerId === null || (int) $sellerId === (int) $onlySellerId;
        });

        return $sellerItems
            ->groupBy(fn (OrderItem $orderItem) => (int) $orderItem->seller_id)
            ->map(function (Collection $orderItems, int|string $sellerId) use ($commissionPayer, $commissionPct, $serviceFeePerItem) {
                $sellerId = (int) $sellerId;
                $seller = $orderItems->first()->seller;

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

    /**
     * Same per-seller/per-line breakdown as sellerInvoiceSummariesForOrder(),
     * but consolidated across every Order placed against this one auction
     * (LiveVideo) — not just a single buyer's order. Used to build one
     * combined invoice per auction for the organizer, instead of one
     * invoice per buyer order, since multiple buyers can each end up with
     * their own Order row for the same live auction.
     *
     * Deliberately a separate method rather than a generalization of
     * sellerInvoiceSummariesForOrder(): that method is also used by the
     * per-order seller-invoice-list endpoint and the admin dashboard invoice
     * view, both of which must keep their existing one-order-at-a-time
     * behavior untouched.
     */
    public static function sellerInvoiceSummariesForLiveVideo(LiveVideo $liveVideo, ?int $onlySellerId = null): Collection
    {
        $liveVideo->loadMissing(['orders.items.seller', 'orders.items.liveVideoItem.pieces', 'orders.items.services']);

        $commissionPayer = $liveVideo->commission_payer ?? 'buyer';
        $commissionPct = (float) ($liveVideo->commission_amount ?? 0);
        $serviceFeePerItem = (float) ($liveVideo->service_fee ?? 0);

        $rows = collect();

        foreach ($liveVideo->orders as $order) {
            foreach ($order->items as $orderItem) {
                // OrderItem.seller_id (not LiveVideoItem.seller_id) is the
                // authoritative, already-resolved seller for this sale —
                // attachWonItem() sets it to the item's own seller_id, or
                // falls back to the item's user_id (the organizer) when the
                // piece is the organizer's own and was never given an
                // explicit seller_id. Using liveVideoItem->seller_id here
                // instead would silently drop the organizer's own pieces
                // whenever that fallback was the only place seller_id ever
                // got resolved.
                //
                // Exception: if LiveVideoItem.seller_id IS explicitly set
                // but disagrees with OrderItem.seller_id, that's a genuine
                // data anomaly, not an own-piece fallback — excluded.
                $sellerId = $orderItem->seller_id;

                if (! $sellerId) {
                    continue;
                }

                $liveVideoItemSellerId = $orderItem->liveVideoItem?->seller_id;
                if ($liveVideoItemSellerId !== null && (int) $liveVideoItemSellerId !== (int) $sellerId) {
                    continue;
                }

                if ($onlySellerId !== null && (int) $sellerId !== (int) $onlySellerId) {
                    continue;
                }

                $rows->push(['order' => $order, 'order_item' => $orderItem, 'seller_id' => (int) $sellerId]);
            }
        }

        return $rows
            ->groupBy('seller_id')
            ->map(function (Collection $group, int|string $sellerId) use ($commissionPayer, $commissionPct, $serviceFeePerItem) {
                $sellerId = (int) $sellerId;
                $seller = $group->first()['order_item']->seller;

                $lines = [];
                $gross = 0.0;
                $commissionTotal = 0.0;
                $serviceTotal = 0.0;
                $pieceServicesTotal = 0.0;
                $net = 0.0;

                foreach ($group as $row) {
                    $order = $row['order'];
                    $orderItem = $row['order_item'];
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
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
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

    /**
     * Total net amount owed to a consignor seller for items sold but not yet
     * settled/paid out (OrderItem.settled_at is null). Same per-line math as
     * sellerInvoiceSummariesForOrder's "net" figure, aggregated across all of
     * that seller's unsettled order items regardless of which order/auction.
     */
    public static function unsettledSellerNet(int $sellerId): float
    {
        $orderItems = OrderItem::where('seller_id', $sellerId)
            ->whereNull('settled_at')
            ->with(['order.liveVideo', 'services'])
            ->get();

        return round($orderItems->sum(function (OrderItem $orderItem) {
            $order = $orderItem->order;
            $live = $order?->liveVideo;
            $commissionPayer = $live?->commission_payer ?? 'buyer';
            $commissionPct = (float) ($live?->commission_amount ?? 0);
            $finished = (float) $orderItem->finished_price;
            $commission = $commissionPayer === 'seller'
                ? round($commissionPct * $finished / 100, 2)
                : 0.0;
            $serviceFee = (float) ($live?->service_fee ?? 0);
            $pieceServices = PieceServiceService::sumItemServicesForOrderItem($orderItem);

            return $finished - $commission - $serviceFee - $pieceServices;
        }), 2);
    }

    /**
     * Total amount a buyer still owes across their unpaid orders.
     */
    public static function unpaidBuyerTotal(int $buyerId): float
    {
        return round((float) Order::where('buyer_id', $buyerId)
            ->where('payment_status', 'unpaid')
            ->sum('total'), 2);
    }
}
