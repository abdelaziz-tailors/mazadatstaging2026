<?php

namespace App\Http\Controllers\api\Video;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddLikeVideoRequest;
use App\Http\Requests\api\Video\AddVideoCommentRequest;
use App\Http\Requests\api\Video\AddVideoReplayCommentRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\VideoCommentResource;
use App\Http\Resources\User\VideoReplayCommentResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Notification;
use App\Models\Video;
use App\Models\VideoComment;
use App\Models\VideoCommentLike;
use App\Models\VideoLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class AuctionVideoController extends Controller
{
    use ResponseTrait;
    public function add(AddVideoCommentRequest $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=LiveVideoItem::find($request->live_video_item_id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video Item not found'));
        }
        if ($video->status != 'working'){
            return $this->failed_response(TranslationHelper::translate(' You cannot add auctions at that time'));
        }
        $data=VideoComment::create([
            'video_id'=>$video->live_video_id,
            'live_video_item_id' => $request->live_video_item_id,
            'comment' => $request->comment,
            'user_id' => auth('api')->user()->id
        ]);

        try {
            $firebase = new FirebaseController();
            $firebase->AddAuctions($data);
        }
        catch(\Exception $t){}

        return $this->success_response(TranslationHelper::translate('Added Successfully '), '');

    }
    public function list($id): JsonResponse
    {
        $video=VideoComment::where('live_video_item_id',$id)->whereNull('comment_id')->get();


        return $this->success_response(TranslationHelper::translate(' Added Successfully '), VideoCommentResource::collection($video));

    }
    public function listReplay($id): JsonResponse
    {
        $video=VideoComment::where('comment_id',$id)->get();


        return $this->success_response(TranslationHelper::translate(' Added Successfully '), VideoReplayCommentResource::collection($video));

    }

    public function addReplay(AddVideoReplayCommentRequest $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=VideoComment::find($request->comment_id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        VideoComment::create([
            'comment_id' => $request->comment_id,
            'comment' => $request->comment,
            'video_id' => $video->video_id,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');
    }
    public function addLike($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=VideoComment::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Comment not found'));
        }
        VideoCommentLike::updateOrCreate([
            'comment_id' => $id,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }
    public function dislike($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=VideoComment::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Comment not found'));
        }
        $video_like=VideoCommentLike::where('comment_id',$id)->where( 'user_id',auth('api')->user()->id)->first();

        if (!$video_like){
            return $this->failed_response(TranslationHelper::translate('Comment not found'));
        }

        $video_like->delete();
        return $this->success_response(TranslationHelper::translate(' Comment DisLike Successfully '), '');

    }








}
