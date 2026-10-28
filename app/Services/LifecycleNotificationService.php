<?php

namespace App\Services;

use App\Models\{FollowUser, LiveVideo, LiveVideoItem, LiveVideoItemFavorite, LiveVideoUser, Order, SellerSubmission, VideoComment};
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LifecycleNotificationService
{
    public const AUCTION_STATUSES = ['pending', 'start', 'end'];
    public const SELLER_STATUSES = ['pending', 'approved', 'rejected', 'needs edit'];
    public const ORDER_STATUSES = ['pending', 'confirmed', 'preparation', 'ready_for_delivery', 'shipping', 'delivered'];

    public function transitioned(Model $entity, array $previous): void
    {
        foreach ($previous as $field => $from) {
            $to = $entity->$field;
            if ($entity instanceof LiveVideo) {
                if (! in_array($to, self::AUCTION_STATUSES, true)) { continue; }
                $type = ['start' => 'auction_started', 'end' => 'auction_ended', 'pending' => 'auction_status_changed'][$to];
                $recipients = $this->auctionRecipients($entity);
                $data = ['live_video_id' => $entity->id];
                $kind = 'auction';
            } elseif ($entity instanceof SellerSubmission) {
                if (! in_array($to, self::SELLER_STATUSES, true)) { continue; }
                $type = 'seller_request_status_changed';
                $recipients = $this->eligibleUsers([$entity->user_id]);
                $data = ['seller_submission_id' => $entity->id];
                $kind = 'seller_submission';
            } elseif ($entity instanceof Order) {
                if (! in_array($to, $field === 'status' ? self::ORDER_STATUSES : ['unpaid', 'paid'], true)) { continue; }
                $type = $field === 'status' ? 'order_status_changed' : 'order_payment_status_changed';
                $recipients = $this->eligibleUsers([$entity->buyer_id]);
                $data = ['order_id' => $entity->id, 'live_video_id' => $entity->live_video_id];
                $kind = 'order';
            } else {
                continue;
            }
            // One occurrence, including legitimate A -> B -> A -> B cycles. Writes and
            // receipts share the entity lock/transaction; rollback discards this UUID.
            $eventId = (string) Str::uuid();
            $data += ['event' => $type, 'event_id' => $eventId, 'entity_type' => $kind,
                'occurred_at' => now()->toISOString(),
                'entity_id' => $entity->id, 'attribute' => $field, 'from_status' => $from, 'to_status' => $to,
                'navigation' => ['entity_type' => $kind, 'entity_id' => $entity->id]];
            foreach ($recipients as $user) {
                $this->send($user, $eventId, $type, $data);
            }
        }
    }

    public function upcoming(LiveVideo $auction): int
    {
        $eventId = 'upcoming_auction:'.$auction->id;
        $count = 0;
        foreach ($this->auctionRecipients($auction) as $user) {
            $this->send($user, $eventId, 'upcoming_auction', [
                'event' => 'upcoming_auction', 'event_id' => $eventId, 'live_video_id' => $auction->id,
                'occurred_at' => now()->toISOString(),
                'entity_type' => 'auction', 'entity_id' => $auction->id,
                'starts_at' => $auction->date_start_at.' '.$auction->time_start_at,
                'navigation' => ['entity_type' => 'auction', 'entity_id' => $auction->id],
            ]);
            $count++;
        }
        return $count;
    }

    public function auctionRecipients(LiveVideo $auction)
    {
        // video_favorites.video_id belongs to Video, not LiveVideo. Item favorites
        // are unambiguous; joining the former by coincidental IDs leaks interest.
        $items = LiveVideoItem::without('pieces')->where('live_video_id', $auction->id)->notTransferredAway();
        $itemIds = (clone $items)->select('id');
        $ids = collect([$auction->user_id, $auction->partner_id])
            ->merge((clone $items)->pluck('seller_id'))
            ->merge((clone $items)->pluck('user_finished_id'))
            ->merge(FollowUser::where('follow_id', $auction->user_id)->whereNotNull('follow_id')->pluck('user_id'))
            ->merge(LiveVideoItemFavorite::whereIn('live_video_item_id', $itemIds)->pluck('user_id'))
            ->merge(LiveVideoUser::where('live_video_id', $auction->id)->pluck('user_id'))
            ->merge(VideoComment::where('video_id', $auction->id)->whereIn('live_video_item_id', $itemIds)
                ->whereNull('comment_id')->pluck('user_id'));
        return $this->eligibleUsers($ids->all());
    }

    private function eligibleUsers(array $ids)
    {
        return User::whereIn('id', array_unique(array_filter($ids)))
            ->where('is_active', 1)->get(['id', 'app_lang']);
    }

    private function send(User $user, string $eventId, string $type, array $data): void
    {
        // Existing dashboard push convention opens the notification inbox. Entity
        // metadata is additive; no undocumented Flutter detail route is invented.
        $data += ['click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'screen' => 'notification'];
        $locale = in_array($user->app_lang, ['en', 'ar'], true) ? $user->app_lang : 'ar';
        $status = isset($data['to_status']) ? trans('notifications.statuses.'.$data['to_status'], [], $locale) : '';
        NotificationService::notifyOnce($user->id, $eventId, $type,
            trans('notifications.'.$type.'.title', [], $locale),
            trans('notifications.'.$type.'.body', ['status' => $status], $locale), $data);
    }
}
