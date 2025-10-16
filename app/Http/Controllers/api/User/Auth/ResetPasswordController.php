<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\VerifyAccountRequest;
use App\Helpers\TranslationHelper;

use App\Models\User\User;

use App\Http\Resources\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;

class ResetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(VerifyAccountRequest $request) {
        $user_exists = User::where('password_otp', $request->otp)->where('type', 'user')->exists();
        if ($user_exists) {
            $user = User::where('password_otp', $request->otp)->where('type', 'user')->first();
            $user->password_otp = NULL;
            $user->save();

            $user['access_token'] = $user->createToken(env('APP_NAME'))->accessToken;
            $data = new UserResource($user);
            return $this->success_response(TranslationHelper::translate('change_your_password'), $data);
        }
        else {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'));
        }
    }
}
