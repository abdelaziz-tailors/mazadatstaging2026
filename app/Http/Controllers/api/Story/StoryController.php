<?php

namespace App\Http\Controllers\api\Story;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Story\AddStoryRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Requests\api\Video\AddViewVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\AllStoryResource;
use App\Http\Resources\User\HomeVideoResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserStoryResource;
use App\Models\Hashtag;
use App\Models\Notification;
use App\Models\Store;
use App\Models\StoreView;
use App\Models\User\User;
use App\Models\Video;
use App\Models\VideoView;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Request;

class StoryController extends Controller
{
    use ResponseTrait;

    public function __invoke(AddStoryRequest $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        if($request->hasfile('image_video')) {
            $stort=$request->file('image_video') ;
            $stort_name = 'story/'.rand(11111, 99999) .'_'.$stort->getClientOriginalName();
            $stort->move(public_path('../storage/app/public/story/'), $stort_name);
        }
        if($request->hasfile('sound')) {
            $sound=$request->file('sound') ;
            $sound_name = 'story_sound/'.rand(11111, 99999) .'_'.$sound->getClientOriginalName();
            $sound->move(public_path('../storage/app/public/story_sound/'), $sound_name);
        }
        $start_at=Carbon::now()->toDateTimeString();
        $end_at = Carbon::parse($start_at)->addHours(24);

        $video=Store::create([
            'type' => $request->file_type,
            'file' => $stort_name,
            'sound'=>$sound_name ?? null,
            'start_at'=>$start_at ?? null,
            'end_at'=>$end_at ?? null,
            'user_id' => auth('api')->user()->id

        ]);



        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }

    public function addView($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $store=Store::find($id);
        if (!$store){
            return $this->failed_response(TranslationHelper::translate('Store not found'));
        }
        StoreView::updateOrCreate([
            'store_id' => $id,
            'user_id' => auth('api')->user()->id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }


    public function myList(\Illuminate\Http\Request $request): JsonResponse
    {

        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $data=Store::where('user_id',auth('api')->user()->id)->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);;


        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => UserStoryResource::collection($data),
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
    public function myActive(): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $today = Carbon::today();

        $data=Store::where('user_id',auth('api')->user()->id)
                    ->whereDate('start_at', '<=', $today)
                    ->whereDate('end_at', '>=', $today)
            ->orderBy('id', 'desc')->get();

//        $data=UserStoryResource::collection($data);
//        return $this->success_response(NULL, $data);

        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => UserStoryResource::collection($data),
        ]);
    }


    public function showList(\Illuminate\Http\Request $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $today = Carbon::today();


        $data = User::whereHas('stories')
            ->with(['stories' => function($query) use ($today) {
                $query->
                    whereDate('start_at', '<=', $today)
                    ->whereDate('end_at', '>=', $today)
                    ->where('user_id','!=',auth('api')->user()->id)
                    ->orderBy('id', 'desc');
            }])

            ->groupBy('id')

            ->paginate((int)$request->video_limit ?? 10);







        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => AllStoryResource::collection($data),
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
