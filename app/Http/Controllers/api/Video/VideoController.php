<?php

namespace App\Http\Controllers\api\Video;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Requests\api\Video\AddViewVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\HomeVideoResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\Hashtag;
use App\Models\LiveVideo;
use App\Models\Notification;
use App\Models\User\User;
use App\Models\Video;
use App\Models\VideoShare;
use App\Models\VideoView;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Request;

class VideoController extends Controller
{
    use ResponseTrait;

    public function __invoke(AddVideoRequest $request): JsonResponse
    {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $file = [];

        if($request->hasfile('image_video')) {

            foreach ($request->file('image_video') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }

        }

        if (isset($request->mention)){

            $mention=json_encode($request->mention);
        }

        if($request->hasfile('sound')) {
            $sound=$request->file('sound') ;
            $sound_name = 'user_sound/'.rand(11111, 99999) .'_'.$sound->getClientOriginalName();
            $sound->move(public_path('../storage/app/public/user_sound/'), $sound_name);
        }

        // dd($request->hasfile('image_video'),json_encode($file));

        $video=Video::create([
            'title' => $request->title,
            'type' => $request->file_type,
            'file' => json_encode($file),
            'sound'=>$sound_name ?? null,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'view_permissions' => $request->view_permissions,
            'comment_permissions' => $request->comment_permissions,
            'mention'=>$mention?? null,
            'user_id' => auth('api')->user()->id

        ]);

        if (isset($request->hashtag)){

            foreach ($request->hashtag as $hashtag) {
                $hashtagInput = str_replace(' ', '_', $hashtag);

                $hashtag = Hashtag::firstOrCreate(['name' => $hashtagInput]);

                // Attach the hashtag to the video
                $video->hashtags()->attach($hashtag->id);
            }

        }


        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }

    public function addView($id,AddViewVideoRequest $request): JsonResponse
    {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $video=LiveVideo::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }


        VideoView::updateOrCreate([
            'video_id' => $id,
            'user_id' => auth('api')->user()->id,
        ]);


        $count=VideoView::where('video_id', $id)->count();
        try {
            $firebase = new FirebaseController();
            $firebase->LiveCount($id,$count);
        }
        catch(\Exception $t){}

        return $this->success_response(TranslationHelper::translate('Added Successfully '), '');

    }
    public function delete($id): JsonResponse
    {
        $video=Video::where('id',$id)->first();
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        $video->delete();
        return $this->success_response(TranslationHelper::translate(' Video Delete Successfully '), '');

    }
    public function addShare($id,AddViewVideoRequest $request): JsonResponse
    {
        $video=Video::find($id);
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        VideoShare::updateOrCreate([
            'video_id' => $id,
            'device_id' => $request->device_id
        ]);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), '');

    }


    public function searchName($name, \Illuminate\Http\Request $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');
        })->where('title','like','%'.$name.'%')->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);


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




    public function searchUserName($name, \Illuminate\Http\Request $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user=User::where(function ($query) use ($name) {
            $query->where('name', 'like','%'.$name.'%')
                ->orWhere('user_name','like','%'.$name.'%');
            })->pluck('id')->toArray();

        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');
        })->whereIn('user_id',$user)->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);


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
