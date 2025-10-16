<?php

namespace App\Http\Controllers\api\Video;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddLikeVideoRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Requests\api\Video\Report\AddVideoReportRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserVideoFavoriteResource;
use App\Models\Notification;
use App\Models\Video;
use App\Models\VideoFavorites;
use App\Models\VideoLike;
use App\Models\VideoReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class VideoReportController extends Controller
{
    use ResponseTrait;


    public function add(AddVideoReportRequest $request,$id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $video=Video::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        VideoReport::updateOrCreate([
            'video_id' => $id,
            'report_id' => $request->report_id?? null,
            'comment' => $request->comment,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }





}
