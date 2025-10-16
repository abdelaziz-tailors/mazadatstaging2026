<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\SocialLoginRequest;
use App\Helpers\TranslationHelper;

use App\Models\Patient;
use App\Models\User\User;

use App\Http\Resources\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;

class SocialLoginController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(SocialLoginRequest $request) {


        if (empty($request->google_id) && empty($request->facebook_id) ) {

                return response()->json(['code' =>200,'success'   => false,'message' => TranslationHelper::translate('Please provide Google ID Or Facebook ID')], 400);
        }

        if (!empty($request->google_id)) {
            $user = Patient::where('google_id', $request->google_id)->first();
        }elseif (!empty($request->facebook_id)){
            $user = Patient::where('facebook_id', $request->facebook_id)->first();
        }
        if (empty($user)) {
            $user = new Patient();
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->phone = $request->phone ?? '';

            if (!empty($request->google_id)) {
                $user->google_id = $request->google_id;
                $user->password =  bcrypt($request->google_id) ?? '';

            } else {
                $user->facebook_id = $request->facebook_id;
                $user->password =  bcrypt($request->facebook_id) ?? '';

            }
            $user->save();
        }

        if (!empty($request->google_id)) {
            $token = Auth::guard('patients')->attempt(['google_id' => $request->google_id, 'password' => $request->google_id]);
        }elseif (!empty($request->facebook_id)){
            $token = Auth::guard('patients')->attempt(['facebook_id' => $request->facebook_id, 'password' =>  $request->facebook_id]);
        }
        $user = auth()->guard('patients')->user();
        $user['token'] = $token;
        $data = new UserResource($user);
        return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
    }
}
