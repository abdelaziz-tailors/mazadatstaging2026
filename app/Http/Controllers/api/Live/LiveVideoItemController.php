<?php

namespace App\Http\Controllers\api\Live;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Http\Requests\api\Provider\Auth\CompeletProfileDoctorRequest;
use App\Http\Requests\api\Provider\Profile\UpdateFcmRequest;
use App\Http\Requests\api\Video\item\StoreItemRequest;
use App\Http\Requests\api\Video\item\updateItemRequest;
use App\Http\Requests\api\Video\UpdateVideoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\User\MyLiveVideoResource;
use App\Http\Resources\User\SingleLiveVideoResource;
use App\Http\Resources\User\VideoCommentResource;
use App\Http\Resources\User\VideoItemResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\VideoComment;
use Illuminate\Http\JsonResponse;


use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class LiveVideoItemController extends Controller
{
    use ResponseTrait;

    public function add(StoreItemRequest $request,$id): JsonResponse
    {

        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $liveVideo =LiveVideo::where('id',$id)->first();

        if (!$liveVideo){
            return $this->failed_response(TranslationHelper::translate('Video Not found'),404);

        }

        if(!$request->partner_id && $liveVideo->partners_type == 'multiple'){
            return $this->failed_response(TranslationHelper::translate('partner_id is required'),422);
        }


        foreach ($request->lineage_title as $index => $title) {
            // Store the image if provided
            $imagePath = null;
            if (!empty($request->image[$index])) {
                        $file = [];
                foreach ($request->image[$index] as $img) {
                $name = 'image_video_item/'.rand(11111, 99999) .'_'.$img->getClientOriginalName();
                    $img->move(public_path('../storage/app/public/image_video_item/'), $name);
                $file[] = $name;
                }
            }
            if ($request->hasFile("health_certificate.$index")) {
                $healthCertificateFile = $request->file("health_certificate.$index");
                $healthCertificateName = 'health_certificate/' . time() . '_' . $healthCertificateFile->getClientOriginalName();
                $healthCertificateFile->move(public_path('storage/health_certificate'), $healthCertificateName);
                $healthCertificatePath = $healthCertificateName;
            }


            // Handle video
            if ($request->hasFile("video.$index")) {
                $videoFile = $request->file("video.$index");
                $videoName = 'video/' . time() . '_' . $videoFile->getClientOriginalName();
                $videoFile->move(public_path('storage/video'), $videoName);
                $videoPath = $videoName;
            }


            // Insert data into the database
            $add_iteam=LiveVideoItem::create([
                'live_video_id' => $id,
                'title' => $title,
                'title_ar'=>$request->lineage_title_ar[$index],
                'status'=>'pending',
                'user_id' => auth('api')->user()->id ?? null,
                'health_certificate' => $healthCertificatePath ?? null,
                'video' => $videoPath ?? null,
                'address'=>$request->address[$index],
                'age'=>$request->age[$index],
                'age_type'=>$request->age_type[$index],
                'type'=>$request->type[$index],
                'image' => json_encode($file),
                'category_id' => $request->category_id[$index] ?? null,
                'information' => $request->information[$index] ?? null,
                'information_ar'=>$request->information_ar[$index],
                'weight' => $request->weight[$index] ?? null,
                'start_price' => $request->start_price[$index] ?? 0,
                'bidding' => $request->bidding[$index] ?? 0,
                'terms'=>$request->terms[$index],
                'color_id'=>$request->color_id[$index],
                'terms_ar'=>$request->terms_ar[$index],
                'quantity'=>$request->quantity[$index] ?? 0,
                'piece_multiplier_number' => $request->piece_multiplier_number[$index] ?? null,
                'identifier' => $request->identifier[$index] ?? null,
                'baham_count' => $request->baham_count[$index] ?? null,
            ]);


            try {
                $firebase = new FirebaseController();
                $firebase->create_item($add_iteam);
            }
            catch(\Exception $t){}

        }


        $data= new MyLiveVideoResource($liveVideo);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);


    }
    public function lastAuction($id): JsonResponse
    {
        $video=VideoComment::where('live_video_item_id',$id)->orderBy('id', 'DESC')->first();



        return $this->success_response(TranslationHelper::translate(' Added Successfully '), new VideoCommentResource($video));

    }


    public function auctionAward(Request $request,$id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }


        if (!$request->comment_id){
            return $this->failed_response(TranslationHelper::translate('please  enter comment '));
        }



        $live_video=LiveVideoItem::where('id',$id)->first();
        if (!$live_video){
            return $this->failed_response(TranslationHelper::translate('Live Video Item Not Found'));
        }

        $hight_pirce=VideoComment::where('id',$request->comment_id)->first();

        $live_video->update([
            'finished_price'=>$hight_pirce->comment,
            'user_finished_id'=>$hight_pirce->user_id,
        ]);


        try {
            $firebase = new FirebaseController();
            $firebase->UserAuctions($hight_pirce);
        }
        catch(\Exception $t){}



        $data= new VideoItemResource($live_video);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }


    public function update(updateItemRequest $request,$id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }



        $live_video=LiveVideoItem::where('id',$id)->first();
        if (!$live_video){
            return $this->failed_response(TranslationHelper::translate('Live Video Not Found'));
        }
        if ($live_video->status !='pending'){
            return $this->failed_response(TranslationHelper::translate('Live Video Cant Be Modified'));
        }
        if(!$request->partner_id && $live_video->videoLive->partners_type == 'multiple'){
            return $this->failed_response(TranslationHelper::translate('partner_id is required'));
        }


        $live_video->update([
        'title_ar' => $request->lineage_title_ar,
        'title'=>$request->lineage_title,
        'address'=>$request->address,
        'status'=>'pending',
        'category_id' => $request->category_id ?? null,
        'information' => $request->information ?? null,
        'information_ar'=>$request->information_ar,
        'terms'=>$request->terms,
        'terms_ar'=>$request->terms_ar,
        'weight' => $request->weight ?? null,
        'age' => $request->age ?? null,
        'age_type'=>$request->age_type,
        'type'=>$request->type,
        'user_id' =>$request->partner_id ?? $live_video->videoLive->partner_id,
        'start_price' => $request->start_price ?? 0,
        'bidding' => $request->bidding ?? 0,
        'color_id'=>$request->color_id,
        'quantity'=>$request->quantity ?? 0,
        'piece_multiplier_number' => $request->piece_multiplier_number ?? null,
        'identifier' => $request->identifier ?? null,
        'baham_count' => $request->baham_count ?? null,
    ]);

        if($request->hasfile('image')) {
            $file=[];

            foreach ($request->file('image') as $image) {
                $name = 'image_video_item/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video_item/'), $name);
                $file[] = $name;
            }
            $live_video->update([
                'image' => json_encode($file),
            ]);
        }

        if($request->hasfile('health_certificate')) {
            $healthCertificateFile = $request->file("health_certificate");
            $healthCertificateName = 'health_certificate/' . time() . '_' . $healthCertificateFile->getClientOriginalName();
            $healthCertificateFile->move(public_path('storage/health_certificate'), $healthCertificateName);
            $live_video->update([
                'health_certificate' => $healthCertificateName,
            ]);

        }

        if($request->hasfile('video')) {
            $videoFile = $request->file("video");
            $videoName = 'video/' . time() . '_' . $videoFile->getClientOriginalName();
            $videoFile->move(public_path('storage/video'), $videoName);
            $live_video->update([
                'video' => $videoName,
            ]);

        }


        $data= new VideoItemResource($live_video);
        return $this->success_response(TranslationHelper::translate(' Update Successfully '), $data);


    }


    public function delete($id): JsonResponse
    {
        $video=LiveVideoItem::where('id',$id)->first();
        if (!$video){
            return $this->failed_response(TranslationHelper::translate('Item not found'));
        }
        if ($video->status !='pending'){
            return $this->failed_response(TranslationHelper::translate('Item Cant Be Deleted'));
        }

        $video->delete();

        try {
            $firebase = new FirebaseController();
            $firebase->removeLiveItem($video->live_video_id,$video->id);
        }
        catch(\Exception $t){}


        return $this->success_response(TranslationHelper::translate('Item Delete Successfully '), '');

    }
    public function start($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $data=LiveVideoItem::find($id);
        $data->update([
            'status'=>'working',
        ]);


        try {
            $firebase = new FirebaseController();
            $firebase->ChangeLiveItemStatus($data->live_video_id,$data->id,'working');
        }
        catch(\Exception $t){}


        $data= new VideoItemResource($data);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }
    public function end($id): JsonResponse
    {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $data=LiveVideoItem::find($id);


        $data->update([
            'status'=>'finished',
            'end_at'=>date('Y-m-d H:i:s'),
        ]);

        try {
            $firebase = new FirebaseController();
            $firebase->ChangeLiveItemStatus($data->live_video_id,$data->id,'finished');
        }
        catch(\Exception $t){}

        $data= new VideoItemResource($data);
        return $this->success_response(TranslationHelper::translate(' Added Successfully '), $data);
    }


}
