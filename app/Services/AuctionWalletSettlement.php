<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class AuctionWalletSettlement
{
    /**
     * Settle all unsettled lines on an order when it transitions to paid.
     *
     * @throws \Throwable
     */
    public static function settleOrderIfNeeded(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            return;
        }

        $order->loadMissing(['items.liveVideoItem.videoLive']);

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $lockedOrder || $lockedOrder->payment_status !== 'paid') {
                return;
            }

            foreach ($lockedOrder->items as $orderItem) {
                self::settleOrderItemIfNeeded($orderItem);
            }

            $lockedOrder->refresh();
            $allSettled = $lockedOrder->items()->whereNull('settled_at')->doesntExist();

            if ($allSettled && ! $lockedOrder->settled_at) {
                $lockedOrder->update(['settled_at' => now()]);
            }
        });
    }

    /**
     * Settle via the order that owns this auction line.
     *
     * @throws \Throwable
     */
    public static function settleIfNeeded(LiveVideoItem $item): void
    {
        $order = OrderService::resolveForItem($item);

        if ($order && $order->payment_status === 'paid') {
            self::settleOrderIfNeeded($order);
        }
    }

    protected static function settleOrderItemIfNeeded(OrderItem $orderItem): void
    {
        $locked = OrderItem::query()
            ->whereKey($orderItem->id)
            ->lockForUpdate()
            ->with(['liveVideoItem.videoLive', 'order'])
            ->first();

        if (! $locked || $locked->settled_at) {
            return;
        }

        $item = $locked->liveVideoItem;
        $live = $item?->videoLive;

        if (! $item || ! $live || ! $item->user_finished_id) {
            return;
        }

        $buyerId = (int) $item->user_finished_id;
        $buyerDebit = self::buyerDebitForItem($item, $live);
        $sellerNet = self::sellerNetForItem($item, $live);
        $partnerCredit = self::partnerCreditForItem($item, $live);
        $partnerUserId = self::resolvePartnerUserId($live);

        self::applyDelta($buyerId, -$buyerDebit, [
            'type' => 'buyer_debit',
            'order_id' => $locked->order_id,
            'order_item_id' => $locked->id,
            'live_video_item_id' => $item->id,
            'description' => 'Auction order payment',
        ]);

        $sellerId = $locked->seller_id ? (int) $locked->seller_id : null;
        if ($sellerId && $sellerNet > 0) {
            self::applyDelta($sellerId, $sellerNet, [
                'type' => 'seller_credit',
                'order_id' => $locked->order_id,
                'order_item_id' => $locked->id,
                'live_video_item_id' => $item->id,
                'description' => 'Auction seller settlement',
            ]);
        }

        if ($partnerUserId && $partnerCredit > 0) {
            self::applyDelta($partnerUserId, $partnerCredit, [
                'type' => 'partner_credit',
                'order_id' => $locked->order_id,
                'order_item_id' => $locked->id,
                'live_video_item_id' => $item->id,
                'description' => 'Auction partner settlement',
            ]);
        }

        $locked->update(['settled_at' => now()]);
    }

    public static function buyerDebitForItem(LiveVideoItem $item, LiveVideo $live): float
    {
        $buyerId = (int) $item->user_finished_id;
        $subTotal = $live->sub_total($buyerId);
        if ($subTotal <= 0) {
            return 0.0;
        }

        $line = (float) ($item->finished_price ?? 0);
        $fraction = $line / $subTotal;

        return round($live->total_price($buyerId) * $fraction, 2);
    }

    public static function sellerNetForItem(LiveVideoItem $item, LiveVideo $live): float
    {
        $finished = (float) ($item->finished_price ?? 0);
        $serviceFee = (float) ($live->service_fee ?? 0);

        if (($live->commission_payer ?? '') === 'seller') {
            $commission = (float) ($live->commission_amount ?? 0) * $finished / 100;

            return round(max(0, $finished - $commission - $serviceFee), 2);
        }

        return round(max(0, $finished - $serviceFee), 2);
    }

    public static function partnerCreditForItem(LiveVideoItem $item, LiveVideo $live): float
    {
        $finished = (float) ($item->finished_price ?? 0);
        $serviceFee = (float) ($live->service_fee ?? 0);
        $commissionAmount = (float) ($live->commission_amount ?? 0) * $finished / 100;
        $taxAmount = (float) ($live->tax_amount ?? 0) * $finished / 100;
        $netPrice = $commissionAmount + $taxAmount + $serviceFee;

        return round(max(0, $netPrice), 2);
    }

    protected static function resolvePartnerUserId(LiveVideo $live): ?int
    {
        if (! $live->partner_id) {
            return null;
        }

        $admin = Admin::query()->find($live->partner_id);
        if ($admin && $admin->user_id) {
            return (int) $admin->user_id;
        }

        if (User::query()->whereKey($live->partner_id)->exists()) {
            return (int) $live->partner_id;
        }

        return null;
    }

    protected static function applyDelta(int $userId, float $delta, array $meta = []): void
    {
        if (abs($delta) < 0.00001) {
            return;
        }

        $user = User::query()->whereKey($userId)->lockForUpdate()->first();

        if (! $user) {
            throw new \RuntimeException('user_not_found');
        }

        $balance = (float) ($user->wallet_balance ?? 0);
        $next = $balance + $delta;

        $user->update(['wallet_balance' => $next]);

        WalletTransaction::query()->create([
            'user_id' => $userId,
            'order_id' => $meta['order_id'] ?? null,
            'order_item_id' => $meta['order_item_id'] ?? null,
            'live_video_item_id' => $meta['live_video_item_id'] ?? null,
            'type' => $meta['type'] ?? 'adjustment',
            'amount' => $delta,
            'balance_after' => $next,
            'description' => $meta['description'] ?? null,
        ]);
    }
}
