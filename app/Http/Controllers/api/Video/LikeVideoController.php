<?php

namespace App\Http\Controllers\api\Video;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddLikeVideoRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoLike;
use App\Models\Notification;
use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class LikeVideoController extends Controller
{
    use ResponseTrait;

    public function __invoke($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');
    }

    public function add($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=LiveVideo::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        LiveVideoLike::updateOrCreate([
            'live_video_id' => $id,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }
    public function dislike($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=LiveVideo::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        $video_like=LiveVideoLike::where( 'live_video_id',$id)->where( 'user_id',auth('api')->user()->id)->first();

        if (!$video_like){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }

        $video_like->delete();
        return $this->success_response(TranslationHelper::translate(' Video DisLike Successfully '), '');

    }





}
