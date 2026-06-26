<?php

namespace App\Console\Commands;

use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BackfillAuctionOrders extends Command
{
    protected $signature = 'orders:backfill';

    protected $description = 'Create orders and order items from existing won auction lines';

    public function handle(): int
    {
        $items = LiveVideoItem::query()
            ->whereNotNull('user_finished_id')
            ->whereNotNull('live_video_id')
            ->orderBy('id')
            ->get();

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $item) {
            OrderService::attachWonItem($item);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->migrateLegacyOrderFields();
        $this->ensureOrderNumbers();
        $this->info('Backfill complete. Orders: '.$items->groupBy(fn ($i) => $i->live_video_id.'-'.$i->user_finished_id)->count());

        return self::SUCCESS;
    }

    protected function migrateLegacyOrderFields(): void
    {
        if (! Schema::hasColumn('live_video_items', 'payment_status')) {
            return;
        }

        $groups = LiveVideoItem::query()
            ->whereNotNull('user_finished_id')
            ->whereNotNull('live_video_id')
            ->get()
            ->groupBy(fn ($item) => $item->live_video_id.'-'.$item->user_finished_id);

        foreach ($groups as $group) {
            $first = $group->first();
            $order = Order::query()
                ->where('live_video_id', $first->live_video_id)
                ->where('buyer_id', $first->user_finished_id)
                ->first();

            if (! $order) {
                continue;
            }

            $paymentStatus = $group->contains(fn ($item) => $item->payment_status === 'paid') ? 'paid' : 'unpaid';
            $statusCart = $first->status_cart ?? 'pending';
            $paymentProof = $group->first(fn ($item) => ! empty($item->payment_proof))?->payment_proof;

            $order->update([
                'payment_status' => $paymentStatus,
                'status' => $statusCart ?: 'pending',
                'payment_proof' => $paymentProof,
            ]);
        }
    }

    protected function ensureOrderNumbers(): void
    {
        Order::query()
            ->whereNull('order_number')
            ->orderBy('id')
            ->each(fn (Order $order) => OrderService::ensureOrderNumber($order));
    }
}
