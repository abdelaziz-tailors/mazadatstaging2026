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

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $lockedOrder || $lockedOrder->payment_status !== 'paid') {
                return;
            }

            $items = $lockedOrder->items()->orderBy('id')->lockForUpdate()->get();
            self::lockSettlementWallets($items);
            foreach ($items as $orderItem) {
                self::settleOrderItemIfNeeded($orderItem);
            }

            $lockedOrder->refresh();
            $allSettled = $lockedOrder->items()->whereNull('settled_at')->doesntExist();

            if ($allSettled && ! $lockedOrder->settled_at) {
                $lockedOrder->update(['settled_at' => now()]);
            }
        }, 3);
    }

    /**
     * Acquire every potential participant wallet in one global ID order.
     * Current locking reads precede snapshots, including on MariaDB snapshot
     * isolation. Shared item/auction/admin locks keep participant mapping stable.
     */
    private static function lockSettlementWallets($items): void
    {
        $ids = [];
        foreach ($items as $line) {
            if ($line->settled_at) { continue; }
            $item = LiveVideoItem::without('pieces')->whereKey($line->live_video_item_id)->sharedLock()->first();
            if (! $item || ! $item->user_finished_id) { continue; }
            $live = LiveVideo::whereKey($item->live_video_id)->sharedLock()->first();
            if (! $live) { continue; }
            $ids[] = $item->user_finished_id;
            $ids[] = $line->seller_id;
            if ($live->partner_id) {
                $admin = Admin::whereKey($live->partner_id)->sharedLock()->first();
                // Lock both candidate IDs. The existing resolver still decides
                // admin-user versus direct-user mapping without formula changes.
                $ids[] = $live->partner_id;
                $ids[] = $admin?->user_id;
            }
        }
        User::whereIn('id', array_values(array_unique(array_filter($ids))))
            ->orderBy('id')->lockForUpdate()->get();
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
            ->with(['liveVideoItem.videoLive', 'order', 'services'])
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
        $sellerNet = self::sellerNetForItem($item, $live, $locked);
        $partnerCredit = self::partnerCreditForItem($item, $live, $locked);
        $partnerUserId = self::resolvePartnerUserId($live);

        // Buying is a payment activity only.
        // Use the actual sold item price (without tax) and do not change
        // the buyer's wallet balance.
        $buyerDebit = round((float) $locked->finished_price, 2);

        self::recordTransactionWithoutBalanceImpact($buyerId, -$buyerDebit, [
            'type' => 'buyer_debit',
            'order_id' => $locked->order_id,
            'order_item_id' => $locked->id,
            'live_video_item_id' => $item->id,
            'description' => 'Auction order payment',
        ]);

        $sellerId = $locked->seller_id ? (int) $locked->seller_id : null;
        $isPartnerOwnPiece = $sellerId
            && $partnerUserId
            && (int) $sellerId === (int) $partnerUserId;

        if ($isPartnerOwnPiece) {
            // Auction owner selling his own piece:
            // one partner transaction for the full sale price.
            self::applyDelta($partnerUserId, round((float) $locked->finished_price, 2), [
                'type' => 'partner_credit',
                'order_id' => $locked->order_id,
                'order_item_id' => $locked->id,
                'live_video_item_id' => $item->id,
                'description' => 'Auction owner sale settlement',
            ]);
        } else {
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

    public static function sellerNetForItem(LiveVideoItem $item, LiveVideo $live, ?OrderItem $orderItem = null): float
    {
        $finished = (float) ($item->finished_price ?? 0);
        $serviceFee = (float) ($live->service_fee ?? 0);
        $pieceServices = $orderItem
            ? PieceServiceService::sumItemServicesForOrderItem($orderItem)
            : PieceServiceService::sumItemServicesForLiveVideoItem($item);

        if (($live->commission_payer ?? '') === 'seller') {
            $commission = (float) ($live->commission_amount ?? 0) * $finished / 100;

            return round(max(0, $finished - $commission - $serviceFee - $pieceServices), 2);
        }

        return round(max(0, $finished - $serviceFee - $pieceServices), 2);
    }

    public static function partnerCreditForItem(LiveVideoItem $item, LiveVideo $live, ?OrderItem $orderItem = null): float
    {
        $finished = (float) ($item->finished_price ?? 0);
        $serviceFee = (float) ($live->service_fee ?? 0);
        $commissionAmount = (float) ($live->commission_amount ?? 0) * $finished / 100;
        $pieceServices = $orderItem
            ? PieceServiceService::sumItemServicesForOrderItem($orderItem)
            : PieceServiceService::sumItemServicesForLiveVideoItem($item);
        $netPrice = $commissionAmount + $serviceFee + $pieceServices;

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

    protected static function recordTransactionWithoutBalanceImpact(
        int $userId,
        float $delta,
        array $meta = []
    ): void {
        if (abs($delta) < 0.00001) {
            return;
        }

        $user = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->first();

        if (! $user) {
            throw new \RuntimeException('user_not_found');
        }

        WalletTransaction::query()->create([
            'user_id' => $userId,
            'order_id' => $meta['order_id'] ?? null,
            'order_item_id' => $meta['order_item_id'] ?? null,
            'live_video_item_id' => $meta['live_video_item_id'] ?? null,
            'type' => $meta['type'] ?? 'adjustment',
            'amount' => $delta,
            'balance_after' => (float) ($user->wallet_balance ?? 0),
            'description' => $meta['description'] ?? null,
        ]);
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
