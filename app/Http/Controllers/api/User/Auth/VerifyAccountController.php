<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\VerifyAccountRequest;
use App\Helpers\TranslationHelper;

use App\Models\User\User;

use App\Http\Resources\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;

class VerifyAccountController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(VerifyAccountRequest $request) {
        $user_exists = User::where('otp', $request->otp)->where('type', 'user')->exists();
        if ($user_exists) {
            $user = User::where('otp', $request->otp)->where('type', 'user')->first();
            if ($user->is_verified) {
                return $this->failed_response(TranslationHelper::translate('your_account_is_verified'));
            }
            else {
                $user->is_verified = true;
                $user->otp = NULL;
                $user->save();

                $user['access_token'] = $user->createToken(env('APP_NAME'))->accessToken;
                $data = new UserResource($user);
                return $this->success_response(TranslationHelper::translate('your_account_is_verified'), $data);
            }
        }
        else {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'));
        }
    }
}
