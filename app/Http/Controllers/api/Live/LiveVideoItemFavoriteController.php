<?php

namespace App\Http\Controllers\api\Live;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\VideoItemResource;
use App\Models\LiveVideoItem;
use App\Models\LiveVideoItemFavorite;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class LiveVideoItemFavoriteController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $items = LiveVideoItem::whereHas('isfavorite')->with('isfavorite')->get();
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), VideoItemResource::collection($items));
    }

    public function add($id): JsonResponse
    {
        if (! LiveVideoItem::whereKey($id)->exists()) {
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        LiveVideoItemFavorite::firstOrCreate([
            'user_id' => auth('api')->id(), 'live_video_item_id' => $id,
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');
    }

    public function unfavorited($id): JsonResponse
    {
        $deleted = LiveVideoItemFavorite::where('user_id', auth('api')->id())
            ->where('live_video_item_id', $id)->delete();
        if (! $deleted) {
            return $this->failed_response(TranslationHelper::translate('Video not In Favorite'));
        }
        return $this->success_response(TranslationHelper::translate(' Video unfavorited  Successfully '), '');
    }
}
