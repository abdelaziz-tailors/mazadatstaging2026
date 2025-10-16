<?php

namespace App\Http\Controllers\api\Home;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\HomeVideoResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\FollowUser;
use App\Models\Hashtag;
use App\Models\LiveVideo;
use App\Models\Notification;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class HomeVideoController extends Controller
{
    use ResponseTrait;

    public function __invoke(Request $request): JsonResponse
    {



        $data = LiveVideo::query();

        if ($request->has('status')) {
            $data->where('status', $request->status);
        }

        if ($request->has('data_from')) {
            $data->where('date_start_at', '>=', $request->data_from);
        }

        if ($request->has('data_to')) {
            $data->where('date_start_at', '<=', $request->data_to);
        }

        $limit = $request->video_limit ?? 10;

        $data = $data->orderBy('id', 'desc')->paginate((int)$limit);





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
    public function userVideos(Request $request,$user_id): JsonResponse
    {



        $data=LiveVideo::where('user_id',$user_id)->

        orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);





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



    public function show($id): JsonResponse
    {



        $data=LiveVideo::find($id);




        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' =>new  HomeVideoResource($data),
        ]);

    }

    public function listHashtag($hashtag, Request $request): JsonResponse
    {
        $hashtagInput = str_replace(' ', '_', $hashtag);

        $hashtag = Hashtag::where('name','like','%'.$hashtagInput.'%')->first();

        if (!$hashtag) {
            return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully','data'=>null]);

        }

        // Get all videos associated with this hashtag
        $data = $hashtag->videos()->
       where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');
        })->

        orderBy('id','desc')->paginate((int)$request->video_limit ?? 10);

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
    public function allListHashtag(): JsonResponse
    {

        $hashtag = Hashtag::all();


        // Get all videos associated with this hashtag

        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => $hashtag,
        ]);

    }



    public function followVideo(Request $request): JsonResponse
    {

        $folow_user=FollowUser::where('user_id',auth('api')->user()->id)->pluck('follow_id')->toArray();
        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');
        })->
        whereIn('user_id',$folow_user)->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);
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








}
