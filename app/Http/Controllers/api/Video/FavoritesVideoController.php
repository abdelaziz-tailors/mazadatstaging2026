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
use App\Http\Resources\User\UserVideoFavoriteResource;
use App\Models\LiveVideo;
use App\Models\Notification;
use App\Models\Video;
use App\Models\VideoFavorites;
use App\Models\VideoLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class FavoritesVideoController extends Controller
{
    use ResponseTrait;

    public function __invoke(): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $VideoFavorites=VideoFavorites::whereHas('video')->where('user_id', auth('api')->user()->id)->get();

        $data=UserVideoFavoriteResource::collection($VideoFavorites);

        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
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
        VideoFavorites::updateOrCreate([
            'video_id' => $id,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }
    public function unfavorited($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $video=LiveVideo::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        $Video_Unfavorite=VideoFavorites::where( 'video_id',$id)->where( 'user_id',auth('api')->user()->id)->first();

        if (!$Video_Unfavorite){
            return $this->failed_response(TranslationHelper::translate('Video not In Favorite'));
        }

        $Video_Unfavorite->delete();
        return $this->success_response(TranslationHelper::translate(' Video unfavorited  Successfully '), '');

    }





}
