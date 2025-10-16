<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\AddShippingAddress;
use App\Http\Requests\api\User\Profile\UpdateFcmRequest;
use App\Http\Requests\api\User\Profile\UpdateLangRequest;
use App\Http\Resources\User\BalanceResource;
use App\Http\Resources\User\CartItemResource;
use App\Http\Resources\User\HomeVideoResource;
use App\Http\Resources\User\NotificationResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\VideoItemResource;
use App\Models\LiveVideoItem;
use App\Models\Notification;
use App\Models\User\User;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Models\ShappingAddress;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class UserProfileController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $data = new ProfileResource(auth('api')->user());
        return $this->success_response(NULL, $data);
    }
    public function balance() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $data = new BalanceResource(auth('api')->user());
        return $this->success_response(NULL, $data);
    }
    public function MyCart() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $data=LiveVideoItem::where('user_finished_id', auth('api')->user()->id)-> orderBy('id', 'desc')->get();
        $data =  CartItemResource::collection($data);

        return $this->success_response(NULL, $data);
    }

    public function addAddress(AddShippingAddress $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }



        // dd($request->all());
        $data=LiveVideoItem::where('id',$request->id)->where('user_finished_id', auth('api')->user()->id)-> orderBy('id', 'desc')->first();

        if (!$data){
            return $this->failed_response(TranslationHelper::translate('Item Not Found'));
        }

        ShappingAddress::updateOrCreate([
            'live_video_item_id' => $request->id,
        ],[
            'address' => $request->shipping_address,
            'city_id' => $request->city_id,
            'payment_method' => $request->payment_method,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);


        $data =  new CartItemResource($data);

        return $this->success_response(NULL, $data);
    }









    public function otherUserprofile($user_name) {
        $user=User::where('user_name',$user_name)->first();

        if (!$user){
            return $this->failed_response(TranslationHelper::translate('User Not Found'));


        }
        $data = new ProfileResource($user);
        return $this->success_response(NULL, $data);
    }




    public function logout(){
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        auth('api')->user()->token()->revoke();
        return $this->success_response(TranslationHelper::translate('Successfully logged out'),'');

    }

    public function deleteAccount(){
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
       auth('api')->user()->delete();

        auth('api')->user()->token()->revoke();
        return $this->success_response(TranslationHelper::translate('Account Deleted Successfully'),'');

    }




    public function updateFcm(UpdateFcmRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }


        $patients = auth('api')->user();
        $patients->update([
            'fcm_token' => $request->fcm_token,
        ]);
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), '');
    }
    public function updateLang(UpdateLangRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }


        $patients = auth('api')->user();
        $patients->update([
            'app_lang' => $request->lang,
        ]);
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), '');
    }


    public function notifications() {
        if (!auth('api')->user()){


            return response()->json(['success'=>false,'code' => 403, 'message' =>TranslationHelper::translate('Un-Authorized Access')], 403);
        }




        $notifications = Notification::where('created_at',">=",auth('api')->user()->created_at)->orderBy('id', 'DESC')->get();



        $data =  NotificationResource::collection($notifications);


        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'),$data );
    }

    public function myVideo(Request $request)
    {
        if (!auth('api')->user()) {


            return response()->json(['success' => false, 'code' => 403, 'message' => TranslationHelper::translate('Un-Authorized Access')], 403);
        }

        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->Where('user_id',auth('api')->user()->id);
        })->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);
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
    public function OtherVideo(Request $request)
    {
        if (!auth('api')->user()) {


            return response()->json(['success' => false, 'code' => 403, 'message' => TranslationHelper::translate('Un-Authorized Access')], 403);
        }


        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');

        })->Where('user_id',auth('api')->user()->id)->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);
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
