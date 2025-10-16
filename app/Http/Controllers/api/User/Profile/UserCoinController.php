<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\SubscriptionRequest;
use App\Http\Requests\api\User\Profile\UpdateFcmRequest;
use App\Http\Resources\PackageResource;
use App\Http\Resources\User\NotificationResource;
use App\Http\Resources\User\PackageSubscriptionResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\Notification;
use App\Models\Package;
use App\Models\User\User;
use App\Models\UserCoin;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class UserCoinController extends Controller
{
    use ResponseTrait;
    public function subscription(SubscriptionRequest $request) {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $image=$request->file('transaction_image') ;
        $image_name = 'user/transaction_image/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
        $image->move(public_path('../storage/app/public/user/transaction_image'), $image_name);

        $package=Package::find($request->package_id);
        UserSubscription::Create([
            'user_id'=>auth('api')->user()->id,
            'package_id'=>$request->package_id,
            'coin'=>$package->coin,
            'price'=>$package->price,
            'image' => $image_name,
        ]);
        $user_coin=UserCoin::where('user_id',auth('api')->user()->id)->sum('coin');
        UserCoin::updateOrCreate([
            'user_id'=>auth('api')->user()->id,
        ],
            [
                'coin'=>$user_coin+$package->coin,
            ]
        );
        return $this->success_response(TranslationHelper::translate('Account Subscription Successfully'), '');
    }
    public function subscriptionList() {
        if (!auth('api')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }
        $user_subscription=UserSubscription::where('user_id',auth('api')->user()->id)->get();
        $data =PackageSubscriptionResource::collection($user_subscription);
        return $this->success_response(TranslationHelper::translate(' Successfully '), $data);

    }




}
