<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class AuctionWalletSettlement
{
    /**
     * Apply wallet movements when an order line is marked paid.
     *
     * Buyer: debit = proportional share of live total (subtotal + tax + buyer commission).
     * Seller: credit = net from SellerInvoiceItemResource rules.
     * Partner: credit = seller commission + service fee (PartnerInvoiceItemResource).
     *
     * Idempotency: only call when transitioning to paid (e.g. !$wasPaid in OrderController).
     *
     * @throws \Throwable
     */
    public static function settleIfNeeded(LiveVideoItem $item): void
    {
        $item->loadMissing('videoLive');
        $live = $item->videoLive;

        if (! $live) {
            return;
        }

        if ($item->payment_status !== 'paid' || ! $item->user_finished_id) {
            return;
        }

        $buyerId = (int) $item->user_finished_id;
        $buyerDebit = self::buyerDebitForItem($item, $live);
        $sellerNet = self::sellerNetForItem($item, $live);
        $partnerCredit = self::partnerCreditForItem($item, $live);
        $partnerUserId = self::resolvePartnerUserId($live);

        DB::transaction(function () use ($item, $buyerId, $buyerDebit, $sellerNet, $partnerCredit, $partnerUserId) {
            $locked = LiveVideoItem::query()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->payment_status !== 'paid') {
                return;
            }

            self::applyDelta($buyerId, -$buyerDebit);

            $sellerId = $locked->seller_id ? (int) $locked->seller_id : null;
            if ($sellerId && $sellerNet > 0) {
                self::applyDelta($sellerId, $sellerNet);
            }

            if ($partnerUserId && $partnerCredit > 0) {
                self::applyDelta($partnerUserId, $partnerCredit);
            }
        });
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
        $commissionAmount = (($live->commission_payer ?? '') === 'seller')
            ? (float) ($live->commission_amount ?? 0) * $finished / 100
            : 0.0;

        return round(max(0, $commissionAmount + $serviceFee), 2);
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

    protected static function applyDelta(int $userId, float $delta): void
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

        // if ($next < 0) {
        //     throw new \RuntimeException('insufficient_wallet_balance');
        // }

        $user->update(['wallet_balance' => $next]);
    }
}
