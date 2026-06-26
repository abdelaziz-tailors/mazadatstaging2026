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
use App\Services\LiveVideoItemPieceService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;


use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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


        foreach ($request->lineage_title_ar as $index => $title) {
            // Store the image if provided
            $imagePath = null;
            if (!empty($request->image[$index])) {
                $file = [];
                foreach ($request->image[$index] as $img) {
                $filename = Str::uuid() . '.' . $img->getClientOriginalExtension();
                $img->move(storage_path('app/public/image_video_item'), $filename);
                $file[] = 'image_video_item/' . $filename;
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
                $filename = Str::uuid() . '.' . $videoFile->getClientOriginalExtension();
                $videoName = 'video/' . $filename;
                $videoFile->move(storage_path('app/public/video'), $filename);
                $videoPath = $videoName;
            }


            // Insert data into the database
            $add_iteam=LiveVideoItem::create([
                'live_video_id' => $id,
                'title_ar'=>$request->lineage_title_ar[$index],
                'status'=>'pending',
                'user_id' => auth('api')->user()->id ?? null,
                'seller_id' => $request->seller_id[$index] ?? null,
                'video' => $videoPath ?? null,
                'address'=>$request->address[$index],
                'type'=>$request->type[$index] ?? null,
                'image' => json_encode($file),
                'category_id' => $request->category_id[$index] ?? null,
                'information_ar'=>$request->information_ar[$index],
                'bidding' => $request->bidding[$index] ?? 0,
                'terms'=>$request->terms[$index] ?? null,
                'color_id'=>$request->color_id[$index]  ?? null,
                'terms_ar'=>$request->terms_ar[$index]  ?? null,
                'quantity'=>$request->quantity[$index] ?? 1,
                'piece_multiplier_number' => $request->piece_multiplier_number[$index] ?? null,
                // 'baham_count' => $request->baham_count[$index] ?? null,
                'health_certificate' => $healthCertificatePath ?? null,
                'title' => $title,
                'information' => $request->information[$index] ?? null,



            ]);

            $itemPieces = $request->input("pieces.$index", []);
            $itemQuantity = (int) ($request->quantity[$index] ?? 1);

            if ($itemQuantity > 1 && ! empty($itemPieces)) {
                LiveVideoItemPieceService::syncPieces($add_iteam, $itemPieces);
            } else {
                LiveVideoItemPieceService::syncPieces($add_iteam, LiveVideoItemPieceService::buildSinglePiecePayload([
                    'age' => $request->age[$index] ?? null,
                    'weight' => $request->weight[$index] ?? null,
                    'identifier' => $request->identifier[$index] ?? null,
                    // 'piece_multiplier_number' => $request->piece_multiplier_number[$index] ?? null,
                    'baham_count' => $request->baham_count[$index] ?? null,
                ]));
            }

            $add_iteam->load('pieces');


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

        OrderService::attachWonItem($live_video->fresh());


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
        // if ($live_video->status !='pending'){
        //     return $this->failed_response(TranslationHelper::translate('Live Video Cant Be Modified'));
        // }
        if ($request->exists('partner_id') && !$request->partner_id && $live_video->videoLive->partners_type == 'multiple') {
            return $this->failed_response(TranslationHelper::translate('partner_id is required'));
        }

        $updateData = ['status' => 'pending'];

        $fieldMap = [
            'lineage_title_ar' => 'title_ar',
            'lineage_title' => 'title',
            'address' => 'address',
            'category_id' => 'category_id',
            'information' => 'information',
            'information_ar' => 'information_ar',
            'terms' => 'terms',
            'terms_ar' => 'terms_ar',
            'type' => 'type',
            'bidding' => 'bidding',
            'color_id' => 'color_id',
            'quantity' => 'quantity',
            'piece_multiplier_number' => 'piece_multiplier_number',
            // 'baham_count' => 'baham_count',
        ];

        foreach ($fieldMap as $requestKey => $dbKey) {
            if ($request->has($requestKey)) {
                $updateData[$dbKey] = $request->input($requestKey);
            }
        }

        if ($request->exists('partner_id')) {
            $updateData['user_id'] = $request->partner_id;
        }

        if ($request->exists('seller_id')) {
            $updateData['seller_id'] = $request->seller_id;
        }

        $live_video->update($updateData);

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

        if ($request->filled('pieces')) {
            LiveVideoItemPieceService::syncPieces($live_video, $request->input('pieces', []));
        } elseif ($request->hasAny(['age', 'weight', 'identifier', 'age_type'])) {
            LiveVideoItemPieceService::syncPieces($live_video, LiveVideoItemPieceService::buildSinglePiecePayload([
                'age' => $request->input('age'),
                'age_type' => $request->input('age_type'),
                'weight' => $request->input('weight'),
                'identifier' => $request->input('identifier'),
                'piece_multiplier_number' => $request->input('piece_multiplier_number', $live_video->piece_multiplier_number),
                'baham_count' => $request->input('baham_count', $live_video->baham_count),
            ]));
        }

        $live_video->load('pieces');


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
        $data=LiveVideoItem::with('pieces')->find($id);
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
        $data=LiveVideoItem::with('pieces')->find($id);


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
