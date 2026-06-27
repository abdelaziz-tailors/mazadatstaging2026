<?php

namespace App\Services;

use App\Helpers\TranslationHelper;
use App\Models\ItemService;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemService;
use Illuminate\Support\Facades\DB;

class PieceServiceService
{
    public static function addToOrderItem(OrderItem $orderItem, array $data): OrderItemService
    {
        self::assertEditableOrderItem($orderItem);

        $service = DB::transaction(function () use ($orderItem, $data) {
            $service = OrderItemService::query()->create([
                'order_item_id' => $orderItem->id,
                'item_service_id' => $data['item_service_id'] ?? null,
                'custom_name' => isset($data['custom_name'])
                    ? json_encode($data['custom_name'])
                    : null,
                'price' => self::resolvePrice($data),
            ]);

            self::recalculateOrderForOrderItem($orderItem);

            return $service;
        });

        return $service->fresh(['itemService', 'orderItem.order']);
    }

    public static function update(OrderItemService $service, array $data): OrderItemService
    {
        $orderItem = $service->orderItem()->with('order')->firstOrFail();
        self::assertEditableOrderItem($orderItem);

        DB::transaction(function () use ($service, $data, $orderItem) {
            $payload = [];
            $merged = [
                'item_service_id' => array_key_exists('item_service_id', $data)
                    ? $data['item_service_id']
                    : $service->item_service_id,
                'price' => array_key_exists('price', $data) ? $data['price'] : $service->price,
            ];

            if (array_key_exists('item_service_id', $data)) {
                $payload['item_service_id'] = $data['item_service_id'];
                $payload['custom_name'] = null;
            }

            if (array_key_exists('custom_name', $data)) {
                $payload['custom_name'] = json_encode($data['custom_name']);
                $payload['item_service_id'] = null;
                $merged['item_service_id'] = null;
            }

            if (array_key_exists('price', $data) || array_key_exists('item_service_id', $data)) {
                $payload['price'] = self::resolvePrice($merged);
            }

            if ($payload !== []) {
                $service->update($payload);
            }

            self::recalculateOrderForOrderItem($orderItem);
        });

        return $service->fresh(['itemService', 'orderItem.order']);
    }

    public static function delete(OrderItemService $service): void
    {
        $orderItem = $service->orderItem()->with('order')->firstOrFail();
        self::assertEditableOrderItem($orderItem);

        DB::transaction(function () use ($service, $orderItem) {
            $service->delete();
            self::recalculateOrderForOrderItem($orderItem);
        });
    }

    public static function assertUserOwnsOrderItem(OrderItem $orderItem, int $userId): Order
    {
        $order = self::resolveOrderForOrderItem($orderItem);

        if (! $order || (int) $order->buyer_id !== $userId) {
            abort(403, TranslationHelper::translate('Un-Authorized Access'));
        }

        if (! self::orderIsEditable($order)) {
            abort(422, TranslationHelper::translate('order_not_editable'));
        }

        return $order;
    }

    public static function assertAdminCanManageOrderItem(OrderItem $orderItem): Order
    {
        $order = self::resolveOrderForOrderItem($orderItem);

        if (! $order) {
            abort(404, TranslationHelper::translate('nothing_found'));
        }

        \App\Support\PartnerDashboardScope::ensureOwnOrder($order);

        if (! self::orderIsEditable($order)) {
            abort(422, TranslationHelper::translate('order_not_editable'));
        }

        return $order;
    }

    public static function resolveOrderForOrderItem(OrderItem $orderItem): ?Order
    {
        $orderItem->loadMissing('order');

        return $orderItem->order;
    }

    public static function orderIsEditable(Order $order): bool
    {
        return $order->payment_status === 'unpaid'
            && $order->status !== 'delivered'
            && $order->settled_at === null;
    }

    public static function recalculateOrderForOrderItem(OrderItem $orderItem): void
    {
        $order = self::resolveOrderForOrderItem($orderItem);

        if ($order) {
            OrderService::recalculateTotals($order->fresh(['items.services']));
        }
    }

    public static function sumItemServicesForOrder(Order $order): float
    {
        $order->loadMissing('items.services');

        $total = 0.0;

        foreach ($order->items as $orderItem) {
            $total += (float) $orderItem->services->sum('price');
        }

        return round($total, 2);
    }

    public static function sumItemServicesForOrderItem(OrderItem $orderItem): float
    {
        $orderItem->loadMissing('services');

        return round((float) $orderItem->services->sum('price'), 2);
    }

    public static function sumItemServicesForLiveVideoItem(LiveVideoItem $item): float
    {
        $item->loadMissing('orderItem.services');

        if (! $item->orderItem) {
            return 0.0;
        }

        return self::sumItemServicesForOrderItem($item->orderItem);
    }

    public static function resolveDefaultPrice(?int $itemServiceId): ?float
    {
        if (! $itemServiceId) {
            return null;
        }

        $type = ItemService::query()->where('is_active', true)->find($itemServiceId);

        return $type?->default_price;
    }

    public static function resolvePrice(array $data): float
    {
        if (array_key_exists('price', $data) && $data['price'] !== null && $data['price'] !== '') {
            return round((float) $data['price'], 2);
        }

        $default = self::resolveDefaultPrice($data['item_service_id'] ?? null);

        if ($default !== null) {
            return round($default, 2);
        }

        abort(422, TranslationHelper::translate('price_required'));
    }

    protected static function assertEditableOrderItem(OrderItem $orderItem): void
    {
        $order = self::resolveOrderForOrderItem($orderItem);

        if (! $order) {
            abort(404, TranslationHelper::translate('nothing_found'));
        }

        if (! self::orderIsEditable($order)) {
            abort(422, TranslationHelper::translate('order_not_editable'));
        }
    }
}
