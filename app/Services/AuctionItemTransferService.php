<?php

namespace App\Services;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuctionItemTransferService
{
    public function transferableItems(LiveVideo $source, ?LiveVideo $target = null): Collection
    {
        if (! $source->isEnded()) {
            return new Collection();
        }

        $query = $source->video_items()
            ->with('pieces', 'seller')
            ->whereNull('finished_price')
            ->whereNull('user_finished_id')
            ->whereDoesntHave('order')
            ->whereDoesntHave('transferredCopies');

        if ($target) {
            $originIds = $target->video_items()
                ->whereNotNull('transfer_origin_item_id')
                ->pluck('transfer_origin_item_id')
                ->all();

            if (! empty($originIds)) {
                $query->whereNotIn(DB::raw('COALESCE(transfer_origin_item_id, id)'), $originIds);
            }
        }

        return $query->orderByDesc('id')->get();
    }

    public function transferItems(LiveVideo $source, LiveVideo $target, array $itemIds): Collection
    {
        if (! $source->isEnded()) {
            throw ValidationException::withMessages([
                'source_auction_id' => ['Items can only be transferred from ended auctions.'],
            ]);
        }

        if ((int) $source->id === (int) $target->id) {
            throw ValidationException::withMessages([
                'target_auction_id' => [__('validation.different', ['attribute' => 'target_auction_id', 'other' => 'source_auction_id'])],
            ]);
        }

        $itemIds = collect($itemIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($itemIds)) {
            throw ValidationException::withMessages([
                'item_ids' => [__('validation.required', ['attribute' => 'item_ids'])],
            ]);
        }

        return DB::transaction(function () use ($source, $target, $itemIds) {
            $items = $source->video_items()
                ->with('pieces')
                ->whereIn('id', $itemIds)
                ->whereNull('finished_price')
                ->whereNull('user_finished_id')
                ->whereDoesntHave('order')
                ->whereDoesntHave('transferredCopies')
                ->lockForUpdate()
                ->get();

            if ($items->count() !== count($itemIds)) {
                throw ValidationException::withMessages([
                    'item_ids' => ['Only unsold items that were not transferred before from this auction can be transferred.'],
                ]);
            }

            $created = new Collection();

            foreach ($items as $item) {
                $originId = (int) ($item->transfer_origin_item_id ?: $item->id);

                $alreadyExists = $target->video_items()
                    ->where('transfer_origin_item_id', $originId)
                    ->exists();

                if ($alreadyExists) {
                    throw ValidationException::withMessages([
                        'item_ids' => ['This item has already been transferred to the selected auction.'],
                    ]);
                }

                $clone = $item->replicate([
                    'transferred_from_item_id',
                    'transfer_origin_item_id',
                    'status',
                    'finished_price',
                    'user_finished_id',
                    'end_at',
                    'winner_video',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]);

                $clone->live_video_id = $target->id;
                $clone->transferred_from_item_id = $item->id;
                $clone->transfer_origin_item_id = $originId;
                $clone->finished_price = null;
                $clone->user_finished_id = null;
                $clone->end_at = null;
                $clone->winner_video = null;
                $clone->status = 'pending';
                $clone->save();

                foreach ($item->pieces->unique('piece_number') as $piece) {
                    $clone->pieces()->create($piece->only([
                        'piece_number',
                        'age',
                        'weight',
                        'identifier',
                        'baham_count',
                    ]));
                }

                $created->push($clone->load('pieces'));
            }

            return $created;
        });
    }

    public function createAuctionAndTransferItems(
        LiveVideo $source,
        array $auctionAttributes,
        array $itemIds,
        ?callable $afterAuctionCreated = null
    ): array {
        return DB::transaction(function () use ($source, $auctionAttributes, $itemIds, $afterAuctionCreated) {
            $target = $this->createAuctionFromSource($source, $auctionAttributes);

            if ($afterAuctionCreated) {
                $afterAuctionCreated($target);
            }

            $items = $this->transferItems($source, $target, $itemIds);

            return [$target, $items];
        });
    }

    public function createAuctionFromSource(LiveVideo $source, array $attributes): LiveVideo
    {
        $target = $source->replicate([
            'status',
            'end_at',
            'upcoming_reminder_sent_at',
            'agora_channel_name',
            'agora_token',
            'agora_app_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        foreach ([
            'title',
            'title_ar',
            'date_start_at',
            'date_end_at',
            'time_start_at',
            'time_end_at',
            'type',
            'start_price',
            'information',
            'information_ar',
            'terms_conditions',
            'terms_conditions_ar',
            'city_id',
        ] as $field) {
            if (array_key_exists($field, $attributes) && $attributes[$field] !== null && $attributes[$field] !== '') {
                $target->{$field} = $attributes[$field];
            }
        }

        $target->status = 'pending';
        $target->is_active = false;
        $target->type = $target->type ?: 'live';
        $target->save();

        return $target;
    }
}
