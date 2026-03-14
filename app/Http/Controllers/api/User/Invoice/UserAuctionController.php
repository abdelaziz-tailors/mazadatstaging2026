<?php

namespace App\Http\Controllers\api\User\Invoice;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\UploadAuctionWinVideoRequest;
use App\Http\Resources\User\AuctionWinVideoResource;
use App\Http\Resources\User\UserInvoiceItemResource;
use App\Http\Resources\User\UserInvoiceResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAuctionController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)  {
        $live=LiveVideo::whereHas('video_items',function($q) use ($request){
            $q->where('user_finished_id',auth('api')->user()->id);
            if($request->data_from){
                $q->where('end_at', '>=', $request->data_from);
            }
            if($request->data_to){
                $q->where('end_at', '<=', $request->data_to);
            }

        })->get();
        $data =  UserInvoiceResource::collection ($live);
        return $this->success_response(NULL, $data);
    }
    public function Iteam($id)
    {
        $live = LiveVideoItem::where('user_finished_id', auth('api')->user()->id)->get();
        $data = UserInvoiceItemResource::collection($live);
        return $this->success_response(NULL, $data);
    }

    /**
     * Upload video for a won auction item.
     * Only the winner (user_finished_id) can upload.
     */
    public function uploadWinVideo(UploadAuctionWinVideoRequest $request, $id): JsonResponse
    {
        $item = LiveVideoItem::where('id', $id)
            ->where('user_finished_id', auth('api')->user()->id)
            ->first();

        if (!$item) {
            return $this->failed_response(TranslationHelper::translate('Auction item not found or you are not the winner'));
        }

        if (!$request->hasFile('video')) {
            return $this->failed_response(TranslationHelper::translate('Please choose a video'));
        }

        $file = $request->file('video');

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['mp4', 'avi', 'wmv', 'mov'])) {
            return $this->failed_response(TranslationHelper::translate('Video must be mp4, avi, wmv or mov'));
        }

        $dir = public_path('auction_win_videos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $file->move($dir, $fileName);
        $relativePath = 'auction_win_videos/' . $fileName;
        $item->update(['winner_video' => $relativePath]);

        return $this->success_response(TranslationHelper::translate('Video uploaded successfully'), new AuctionWinVideoResource($item->fresh()));
    }
}
