<?php

namespace App\Http\Controllers\api\User;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
{
    use ResponseTrait;

    public function checkPromoCode(Request $request)//: JsonResponse
    {
        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }


        $validator = Validator::make($request->all(), [
            'promo_code' => 'required',
        ]);
        if ($validator->fails())
        {
            return $this->failed_response($validator->errors()->first());
        }
        $promoCode = PromoCode::withCount('reservations')
            // ->active()
            ->where('promo_code',$request->promo_code)
            ->orderBy('id','desc')
            ->first();

        if(!$promoCode){
            return $this->failed_response(TranslationHelper::translate('no valid Promo-code Found'));
        }
        elseif (
            (date('Y-m-d',strtotime($promoCode->start_date)) > date('Y-m-d'))  ||
            (date('Y-m-d',strtotime($promoCode->expired_date)) <=  date('Y-m-d'))
         ) {
            return $this->failed_response(TranslationHelper::translate('This Promocode is not valid in this date'));
        }
        else{
            if($promoCode->used_number <= $promoCode->reservations()->count() ){
                return $this->failed_response(TranslationHelper::translate('This Promocode exceed the number of use'));
            }
        }
        return $this->success_response(TranslationHelper::translate('Data Fetched Successfully'), $promoCode);
    }

}
