<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ResetPasswordRequest;
use App\Helpers\TranslationHelper;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;

class ResetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(ResetPasswordRequest $request)
    {
        $user = User::where('phone', $request->phone)
            ->where('password_otp', $request->otp)
            ->first();

        if (! $user) {
            return $this->failed_response(TranslationHelper::translate('wrong_otp'));
        }

        $user->password = bcrypt($request->password);
        $user->password_otp = null;
        $user->save();

        $user->token = $user->createToken('MyApp')->accessToken;

        return $this->success_response(
            TranslationHelper::translate('your_password_changed_successfully'),
            new UserResource($user)
        );
    }
}
