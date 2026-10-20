<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ResetPasswordRequest;
use App\Helpers\TranslationHelper;
use App\Http\Resources\User\UserResource;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(ResetPasswordRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $this->passwordResetUser($request->phone);

            if (! $user || ! $user->is_verified || ! $user->password_otp
                || ! hash_equals((string) $user->password_otp, $request->otp)) {
                return $this->failed_response(TranslationHelper::translate('wrong_otp'));
            }

            if (! $user->expire_at || Carbon::now()->greaterThanOrEqualTo($user->expire_at)) {
                return $this->failed_response(TranslationHelper::translate('otp expire'), 403);
            }

            $user->password = bcrypt($request->password);
            $user->password_otp = null;
            $user->expire_at = null;
            $user->save();

            $user->token = $user->createToken('MyApp')->accessToken;

            return $this->success_response(
                TranslationHelper::translate('your_password_changed_successfully'),
                new UserResource($user)
            );
        });
    }
}
