<?php

namespace App\Http\Controllers\api\User\Friend;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddLikeVideoRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\FollowProfileResource;
use App\Http\Resources\User\FriendProfileResource;
use App\Http\Resources\User\HomeVideoResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserDataResource;
use App\Http\Resources\User\UserResource;
use App\Models\FollowUser;
use App\Models\Friend;
use App\Models\Notification;
use App\Models\User\User;
use App\Models\Video;
use App\Models\VideoLike;
use App\Models\VideoView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class FriendController extends Controller
{
    use ResponseTrait;


    public function search(Request $request): JsonResponse
    {

        $user=User::where(function ($query) use ($request) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('user_name', 'like', '%'.$request->search.'%');
        })->get();
        $data =  UserDataResource::collection ($user);
        return $this->success_response(NULL, $data);


    }

    public function suggest(Request $request): JsonResponse
    {

        $suggest=json_encode($request->phone);
        $suggest_arrat=json_decode($suggest);
        $user=User::whereIn('phone',$suggest_arrat)->get();
        $data =  UserDataResource::collection ($user);
        return $this->success_response(NULL, $data);


    }
    public function add($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=User::find($id);
        if (!$user){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        Friend::updateOrCreate([
            'friend_id' => $id,
            'user_id' => auth('api')->user()->id,
            'type' => 'request'
        ]);
        return $this->success_response(TranslationHelper::translate(' Friend request send  Successfully '), '');

    }
    public function acceptFriendRequest($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=User::find($id);
        if (!$user){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        Friend::updateOrCreate([
            'user_id' => $id,
            'friend_id' => auth('api')->user()->id,
            ],
            [
            'type' => 'friend'
        ]);
        return $this->success_response(TranslationHelper::translate(' Friend request Accept  Successfully '), '');

    }




    public function unfriend($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=User::find($id);
        if (!$user){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        $follow=Friend::where( 'friend_id',$id)->where( 'user_id',auth('api')->user()->id)->first();

        if (!$follow){
            return $this->failed_response(TranslationHelper::translate('Friend not found'));
        }

        $follow->delete();
        return $this->success_response(TranslationHelper::translate(' Unfriend User Successfully '), '');

    }
    public function requestList(): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=Friend::where('type','request')->where('friend_id',auth('api')->user()->id)->get();
        $data =  FriendProfileResource::collection ($user);
        return $this->success_response(NULL, $data);
    }
    public function List(): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=Friend::where(function ($query) {
            $query->where('friend_id',auth('api')->user()->id)
                ->orWhere('user_id',auth('api')->user()->id);
        })->where('type','friend')->get();
        $data =  FriendProfileResource::collection ($user);
        return $this->success_response(NULL, $data);
    }



    public function myRequestList(): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=Friend::where('type','request')->where('user_id',auth('api')->user()->id)->get();
        $data =  FollowProfileResource::collection ($user);
        return $this->success_response(NULL, $data);
    }

    public function video( Request $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $frindes=Friend::where('friend_id',auth('api')->user()->id)
            ->where('type','friend')->pluck('user_id')->toArray();

        $my_frindes=Friend::where('user_id',auth('api')->user()->id)
            ->where('type','friend')->pluck('friend_id')->toArray();
        $user_vide=array_merge($my_frindes,$frindes);

        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');
        })->

        where('user_id','!=',auth('api')->user()->id)->whereIn('user_id',$user_vide)->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);


        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => HomeVideoResource::collection($data),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'links' => [
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ],
            ],
        ]);
    }

    public function viewCount(): JsonResponse
    {
        if (!auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $frindes=Friend::where('friend_id',auth('api')->user()->id)
            ->where('type','friend')->pluck('user_id')->toArray();

        $my_frindes=Friend::where('user_id',auth('api')->user()->id)
            ->where('type','friend')->pluck('friend_id')->toArray();
        $user_vide=array_merge($my_frindes,$frindes);

        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');
        })->where('user_id','!=',auth('api')->user()->id)->whereIn('user_id',$user_vide)->pluck('id')->toArray();


        $data=[
            'count'=>count($data)
        ];




        return $this->success_response(NULL, $data);

    }


}
