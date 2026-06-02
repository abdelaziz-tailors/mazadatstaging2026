<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\VerifyAccountRequest;
use App\Helpers\TranslationHelper;

use App\Models\User\User;

use App\Http\Resources\User\UserResource;

use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;

class VerifyAccountController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(VerifyAccountRequest $request) {
        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return $this->failed_response(TranslationHelper::translate('wrong_phone_number'));
        }

        if ((string) $user->otp !== (string) $request->otp) {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'));
        }

        $now = Carbon::now();
        if (! $user->expire_at || $now->isAfter($user->expire_at)) {
            return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
        }

        $user->is_verified = true;
        $user->otp = null;
        $user->expire_at = null;
        $user->save();

        $user['token'] = $user->createToken('MyApp')->accessToken;
        $data = new UserResource($user);

        return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
    }
}
