<?php

namespace App\Http\Controllers\api\Live;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\User\Profile\GiftRequest;
use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Requests\api\Video\UpdateVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\MyLiveVideoResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\SingleLiveVideoResource;
use App\Http\Resources\User\UserLiveVideoResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserViewVideoResource;
use App\Http\Resources\User\VideoCommentResource;
use App\Jobs\SendFCMNotification;
use App\Models\Gift;
use App\Models\LiveVideo;
use App\Models\LiveVideoLike;
use App\Models\LiveVideoUser;
use App\Models\Notification;
use App\Models\Package;
use App\Models\User\User;
use App\Models\UserCoin;
use App\Models\UserGift;
use App\Models\UserSubscription;
use App\Models\Video;
use App\Models\VideoComment;
use App\Models\VideoView;
use App\Services\AgoraService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;


use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class LiveVideoController extends Controller
{
    use ResponseTrait;

    public function add(AddVideoRequest $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $file = [];

        if($request->hasfile('image')) {

            foreach ($request->file('image') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }
        }

        if(auth('api')->user()->admin->partner == 'partner' ? true : false){
            return $this->failed_response('هذه الخاصية غير متاحة لك لانك ليست شريك', '422');
        }else{

        $admin_id= auth('api')->user()->admin->id ?? null;

        // Generate Agora channel and tokens for live streaming
        $agoraService = new AgoraService();
        $channelName = $agoraService->generateChannelName(time());
        $userId = auth('api')->user()->id;
        $agoraCredentials = $agoraService->generateLiveStreamCredentials($channelName, $userId);

        $data=LiveVideo::create([
            'user_id' => auth('api')->user()->id,
            'title'=>$request->title,
            'title_ar'=>$request->title_ar,
            'status' => 'pending',
            'image' => json_encode($file),
            'information' => $request->information,
            'information_ar' => $request->information_ar,
            'date_start_at' => $request->date_start_at,
            'date_end_at' => $request->date_end_at,
            'time_start_at' => $request->time_start_at,
            'time_end_at' => $request->time_end_at,
            'terms_conditions' => $request->terms_conditions,
            'terms_conditions_ar' => $request->terms_conditions_ar,
            'city_id' => $request->city_id ?? null,
            'admin_id' => $admin_id,
            'partner_id' => auth('api')->user()->admin->id ?? null,
            'type' => $request->video_type ?? 'live',
            'partners_type' => 'single',
            'agora_channel_name' => $channelName,
            'agora_token_publisher' => $agoraCredentials['token_publisher'],
            'agora_token_subscriber' => $agoraCredentials['token_subscriber'],
            'agora_app_id' => $agoraCredentials['app_id'],
        ]);
        try {
            $firebase = new FirebaseController();
            $firebase->create($data);
        
        }
        catch(\Exception $t){}


        $tokens_en = User::whereNotNull('fcm_token')->where('app_lang', 'en')->pluck('fcm_token')->toArray();
        $tokens_ar = User::whereNotNull('fcm_token')->where('app_lang', 'ar')->pluck('fcm_token')->toArray();
        $notification_record = [
            'title_en' => 'New Auction: ' . $data->title,
            'title_ar' => 'مزاد جديد: ' . $data->title_ar,
            'body_en'  => 'Auction "' . $data->title . '" will be held on ' . $data->date_start_at . ' at ' . $data->time_start_at,
            'body_ar'  => 'سيقام المزاد "' . $data->title . '" في ' . $data->date_start_at . ' في ' . $data->time_start_at,
        ];
                // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_en,
            $notification_record['title_en'],
            $notification_record['body_en'],
        ));
        // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_ar,
            $notification_record['title_ar'],
            $notification_record['body_ar'],
        ));


        $data= new MyLiveVideoResource($data);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
}
    public function update(UpdateVideoRequest $request,$id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }



        $live_video=LiveVideo::where('id',$id)->where('user_id',auth('api')->user()->id)->first();
        if (!$live_video){
            return $this->failed_response(TranslationHelper::translate('Live Video Not Found'));
        }
        if ($live_video->status !='pending'){
            return $this->failed_response(TranslationHelper::translate('Live Video Cant Be Modified'));
        }

        $admin_id=auth('api')->user()->admin->id;

        $live_video->update([
            'title'=>$request->title,
            'title_ar'=>$request->title_ar,
            'user_id' => auth('api')->user()->id,
            'information' => $request->information,
            'information_ar' => $request->information_ar,
            'date_start_at' => $request->date_start_at,
            'date_end_at' => $request->date_end_at,
            'time_start_at' => $request->time_start_at,
            'time_end_at' => $request->time_end_at,
            'terms_conditions' => $request->terms_conditions,
            'terms_conditions_ar' => $request->terms_conditions_ar,
            'city_id' => $request->city_id,
            'admin_id' => $admin_id,
            'partner_id' => $request->partner_id ?? null,
            'type' => $request->video_type,
            'partners_type' => $request->partners_type,
        ]);


        if($request->hasfile('image')) {
            $file=[];

            foreach ($request->file('image') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }
            $live_video->update([
                'image' => json_encode($file),
            ]);
        }

        $data= new MyLiveVideoResource($live_video);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
    public function auctionAward(Request $request,$id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }


        if (!$request->comment_id){
            return $this->failed_response(TranslationHelper::translate('please  enter comment '));
        }



        $live_video=LiveVideo::where('id',$id)->where('user_id',auth('api')->user()->id)->first();
        if (!$live_video){
            return $this->failed_response(TranslationHelper::translate('Live Video Not Found'));
        }

        $hight_pirce=VideoComment::where('id',$request->comment_id)->first();

        $live_video->update([
            'price'=>$hight_pirce->comment,
            'user_price_id'=>$hight_pirce->user_id,
        ]);
        $data= new MyLiveVideoResource($live_video);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
    public function lastAuction($id): JsonResponse
    {
        $video=VideoComment::where('video_id',$id)->orderBy('id', 'DESC')->first();



        return $this->success_response(TranslationHelper::translate(' Added Successfully '), new VideoCommentResource($video));

    }



    public function start($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $data=LiveVideo::find($id);
        $data->update([
            'status'=>'start',
        ]);

        try {
            $firebase = new FirebaseController();
            $firebase->ChangeLiveStatus($id,'start');
        }
        catch(\Exception $t){}


        $tokens_en = User::whereNotNull('fcm_token')->where('app_lang', 'en')->pluck('fcm_token')->toArray();
        $tokens_ar = User::whereNotNull('fcm_token')->where('app_lang', 'ar')->pluck('fcm_token')->toArray();
        $notification_record = [
            'title_en' => 'Auction Started: ' . $data->title,
            'title_ar' => 'بدأ المزاد: ' . $data->title_ar,
            'body_en'  => 'Auction "' . $data->title . '" has started on ' . $data->date_start_at . ' at ' . $data->time_start_at,
            'body_ar'  => 'بدأ المزاد "' . $data->title . '" بتاريخ ' . $data->date_start_at . ' في ' . $data->time_start_at,
        ];
        // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_en,
            $notification_record['title_en'],
            $notification_record['body_en'],
        ));
        // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_ar,
            $notification_record['title_ar'],
            $notification_record['body_ar'],
        ));



        $data= new MyLiveVideoResource($data);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
    public function end($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $data=LiveVideo::find($id);

        $hight_pirce=VideoComment::where('video_id',$id)->orderByDesc('comment')->first();

        $data->update([
            'status'=>'end',
//            'price'=>$hight_pirce->comment,
//            'user_price_id'=>$hight_pirce->user_id,
            'end_at'=>date('Y-m-d H:i:s'),
        ]);
        try {
            $firebase = new FirebaseController();
            $firebase->removeLive($id);
        }
        catch(\Exception $t){}


        $tokens_en = User::whereNotNull('fcm_token')->where('app_lang', 'en')->pluck('fcm_token')->toArray();
        $tokens_ar = User::whereNotNull('fcm_token')->where('app_lang', 'ar')->pluck('fcm_token')->toArray();
        $notification_record = [
            'title_en' => 'Auction Ended: ' . $data->title,
            'title_ar' => 'انتهى المزاد: ' . $data->title_ar,
            'body_en'  => 'Auction "' . $data->title . '" has ended on ' . $data->date_end_at . ' at ' . $data->time_end_at,
            'body_ar'  => 'انتهى المزاد "' . $data->title . '" بتاريخ ' . $data->date_end_at . ' في ' . $data->time_end_at,
        ];
        // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_en,
            $notification_record['title_en'],
            $notification_record['body_en'],
        ));
        // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_ar,
            $notification_record['title_ar'],
            $notification_record['body_ar'],
        ));



        $data= new MyLiveVideoResource($data);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }

    public function myVideoViewList($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
         $data=VideoView::where('video_id',$id)->get();
        $data=  UserViewVideoResource::collection($data);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }

    public function myList(Request $request): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        if($request->has('status')){
            $data=LiveVideo::where('user_id',auth('api')->user()->id)->where('status',$request->status)->get();
        }else{
            $data=LiveVideo::where('user_id',auth('api')->user()->id)->get();
        }
        $data= MyLiveVideoResource::collection($data);

        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
    public function SingleVideo($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $data=LiveVideo::find($id);
        $data= new SingleLiveVideoResource($data);

        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
    public function delete($id): JsonResponse
    {
        $video=LiveVideo::where('id',$id)->where('user_id',auth('api')->user()->id)->first();
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Video not found'));
        }
        if ($video->status !=null){
            return $this->failed_response(TranslationHelper::translate('Live Video Cant Be Modified'));
        }

        $video->delete();
        try {
            $firebase = new FirebaseController();
            $firebase->removeLive($id);
        }
        catch(\Exception $t){}


        return $this->success_response(TranslationHelper::translate(' Video Delete Successfully '), '');

    }

   

}
