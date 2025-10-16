<?php

namespace App\Http\Controllers\api\User;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddLikeVideoRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\FollowProfileResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\FollowUser;
use App\Models\Notification;
use App\Models\User\User;
use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class FollowUserController extends Controller
{
    use ResponseTrait;


    public function add($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=User::find($id);
        if (!$user){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        FollowUser::updateOrCreate([
            'follow_id' => $id,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }
    public function list(): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=FollowUser::where('user_id',auth('api')->user()->id)->get();
        $data =  FollowProfileResource::collection ($user);
        return $this->success_response(NULL, $data);


    }
    public function followersList($id): JsonResponse
    {
//        if (!auth('api')->user()){
//            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
//        }
        $user=FollowUser::where('follow_id',$id)->get();
        $data =  FollowProfileResource::collection ($user);
        return $this->success_response(NULL, $data);


    }



    public function unfollow($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=User::find($id);
        if (!$user){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        $follow=FollowUser::where( 'follow_id',$id)->where( 'user_id',auth('api')->user()->id)->first();

        if (!$follow){
            return $this->failed_response(TranslationHelper::translate('User Follow not found'));
        }

        $follow->delete();
        return $this->success_response(TranslationHelper::translate(' Unfollow User Successfully '), '');

    }





}
