<?php

namespace App\Http\Controllers\api\Live;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\MyLiveVideoResource;
use App\Http\Resources\User\VideoItemResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\UserSubscription;
use App\Services\AuctionItemTransferService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LiveVideoItemTransferController extends Controller
{
    use ResponseTrait;

    public function transferable($id, Request $request, AuctionItemTransferService $transferService): JsonResponse
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        // When a destination is supplied, mobile clients may pass the item
        // ID in the URL. Resolve its source auction automatically.
        $item = LiveVideoItem::find($id);
        $source = $item && ($request->filled('target_auction_id') || $request->filled('target_location_id'))
            ? $this->ownedAuction($item->live_video_id)
            : $this->ownedAuction($id);
        if (! $source) {
            return $this->failed_response(TranslationHelper::translate('Video Not found'), 404);
        }

        $target = null;
        // target_location_id is accepted as an alias by mobile clients. It
        // represents the destination auction (live_videos.id), not cities.id.
        $targetAuctionId = $request->input('target_auction_id', $request->input('target_location_id'));
        if ($targetAuctionId) {
            $target = $this->ownedAuction($targetAuctionId);
            if (! $target) {
                return $this->failed_response(TranslationHelper::translate('Video Not found'), 404);
            }
        }

        $items = $transferService->transferableItems($source, $target);

        if ($item && ($request->filled('target_auction_id') || $request->filled('target_location_id'))) {
            $items = $items->where('id', (int) $id)->values();
        }

        return $this->success_response(
            TranslationHelper::translate('Added Successfully'),
            VideoItemResource::collection($items)
        );
    }

    public function transfer(Request $request, AuctionItemTransferService $transferService): JsonResponse
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        // TEMPORARY diagnostic logging for the mobile existing-auction
        // transfer investigation. No secrets/tokens/Agora values. Remove
        // once the production root cause is confirmed.
        Log::info('MOBILE_TRANSFER_REQUEST', [
            'user_id' => auth('api')->user()->id,
            'path' => $request->path(),
            'item_id' => $request->input('item_id'),
            'item_ids' => $request->input('item_ids'),
            'source_auction_id' => $request->input('source_auction_id'),
            'target_auction_id' => $request->input('target_auction_id'),
            'target_location_id' => $request->input('target_location_id'),
            'transfer_mode' => $request->input('transfer_mode'),
        ]);

        try {
            // Normalize both the single-item mobile payload and the original
            // array payload before validation.
            $itemIds = $request->input('item_ids');
            if ($itemIds === null && $request->filled('item_id')) {
                $itemIds = [$request->input('item_id')];
            } elseif (! is_array($itemIds)) {
                $itemIds = [$itemIds];
            }

            $targetAuctionId = $request->input('target_auction_id', $request->input('target_location_id'));
            $transferMode = $request->input('transfer_mode');
            if (! $transferMode && $targetAuctionId) {
                $transferMode = 'existing';
            }

            $request->merge([
                'item_ids' => $itemIds,
                'target_auction_id' => $targetAuctionId,
                'transfer_mode' => $transferMode,
            ]);

            // For a single-item mobile request, derive the source auction
            // from live_video_items instead of requiring another ID.
            if (! $request->filled('source_auction_id') && $request->filled('item_id')) {
                $item = LiveVideoItem::find($request->input('item_id'));
                if ($item) {
                    $request->merge(['source_auction_id' => $item->live_video_id]);
                }
            }

            Log::info('MOBILE_TRANSFER_NORMALIZED', [
                'source_auction_id' => $request->input('source_auction_id'),
                'target_auction_id' => $request->input('target_auction_id'),
                'item_ids' => $request->input('item_ids'),
                'transfer_mode' => $request->input('transfer_mode'),
            ]);

            $request->validate([
                'source_auction_id' => 'required|integer|exists:live_videos,id',
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:live_video_items,id',
                'transfer_mode' => 'required|in:existing,new',
                'target_auction_id' => 'required_if:transfer_mode,existing|nullable|integer|exists:live_videos,id',
                'new_auction.type' => 'required_if:transfer_mode,new|nullable|in:live,recorded,photo',
                'new_auction.title_ar' => 'required_if:transfer_mode,new|nullable|string|max:255',
                'new_auction.title' => 'nullable|string|max:255',
                'new_auction.date_start_at' => 'required_if:transfer_mode,new|nullable|date',
                'new_auction.date_end_at' => 'required_if:transfer_mode,new|nullable|date|after_or_equal:new_auction.date_start_at',
                'new_auction.time_start_at' => 'required_if:transfer_mode,new|nullable',
                'new_auction.time_end_at' => 'required_if:transfer_mode,new|nullable',
                'new_auction.start_price' => 'required_if:transfer_mode,new|nullable|numeric|min:0',
                'new_auction.information_ar' => 'nullable|string',
                'new_auction.information' => 'nullable|string',
                'new_auction.terms_conditions_ar' => 'nullable|string',
                'new_auction.terms_conditions' => 'nullable|string',
                'new_auction.city_id' => 'nullable|integer|exists:cities,id',
            ]);

            $source = $this->ownedAuction($request->source_auction_id);
            if (! $source) {
                Log::info('MOBILE_TRANSFER_FAILED', [
                    'error' => 'source auction not found or not owned by authenticated user',
                ]);
                return $this->failed_response(TranslationHelper::translate('Video Not found'), 404);
            }

            if ($request->transfer_mode === 'existing') {
                $target = $this->ownedAuction($request->target_auction_id);
                if (! $target) {
                    Log::info('MOBILE_TRANSFER_FAILED', [
                        'error' => 'target auction not found or not owned by authenticated user',
                    ]);
                    return $this->failed_response(TranslationHelper::translate('Video Not found'), 404);
                }
                $createdItems = $transferService->transferItems($source, $target, $request->item_ids);

                $target = $target->fresh(['video_items']);
                $targetId = (int) $request->target_auction_id;
                $createdItemIds = $createdItems->pluck('id')->filter()->values();

                if ((int) $target->id !== $targetId || $createdItemIds->isEmpty() ||
                    $createdItems->contains(function (LiveVideoItem $createdItem) use ($targetId) {
                        return (int) $createdItem->live_video_id !== $targetId;
                    }) ||
                    $createdItemIds->diff($target->video_items->pluck('id'))->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'target_auction_id' => ['The transferred item was not returned by the selected destination auction.'],
                    ]);
                }

                Log::info('MOBILE_TRANSFER_SUCCESS', [
                    'target_auction_id' => $target->id,
                    'created_item_ids' => $createdItemIds->all(),
                    'created_item_live_video_ids' => $createdItems->pluck('live_video_id')->all(),
                ]);
            } else {
                $activeSubscription = UserSubscription::getActiveSubscription(auth('api')->user()->id);
                if (! $activeSubscription) {
                    return $this->failed_response(
                        TranslationHelper::translate('You need to subscribe to create auctions. Please subscribe to continue.'),
                        422
                    );
                }

                $newAuction = $request->input('new_auction', []);
                [$target, $createdItems] = $transferService->createAuctionAndTransferItems($source, [
                    'title' => $newAuction['title'] ?? ($newAuction['title_ar'] ?? null),
                    'title_ar' => $newAuction['title_ar'] ?? null,
                    'type' => $newAuction['type'] ?? 'live',
                    'date_start_at' => $newAuction['date_start_at'] ?? null,
                    'date_end_at' => $newAuction['date_end_at'] ?? null,
                    'time_start_at' => $newAuction['time_start_at'] ?? null,
                    'time_end_at' => $newAuction['time_end_at'] ?? null,
                    'start_price' => $newAuction['start_price'] ?? null,
                    'information' => $newAuction['information'] ?? null,
                    'information_ar' => $newAuction['information_ar'] ?? null,
                    'terms_conditions' => $newAuction['terms_conditions'] ?? null,
                    'terms_conditions_ar' => $newAuction['terms_conditions_ar'] ?? null,
                    'city_id' => $newAuction['city_id'] ?? null,
                ], $request->item_ids, function () use ($activeSubscription) {
                    $activeSubscription->decrementAuctions();
                });
            }

            return $this->success_response(TranslationHelper::translate('items_transferred_successfully'), [
                'target_auction' => new MyLiveVideoResource($target->fresh('video_items')),
                'transferred_items' => VideoItemResource::collection($createdItems),
            ]);
        } catch (ValidationException $exception) {
            Log::info('MOBILE_TRANSFER_FAILED', [
                'error' => $exception->validator->errors()->first(),
            ]);
            return $this->failed_response($exception->validator->errors()->first(), 422);
        }
    }

    private function ownedAuction($id): ?LiveVideo
    {
        return LiveVideo::where('id', $id)
            ->where('user_id', auth('api')->user()->id)
            ->first();
    }
}
