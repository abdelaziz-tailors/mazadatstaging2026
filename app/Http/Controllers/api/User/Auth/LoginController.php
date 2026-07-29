<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\TranslationHelper;
use App\Models\User\User;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;

class LoginController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(LoginRequest $request) {

        $loginField = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if ($token = Auth::attempt([$loginField => $request->phone, 'password' => request('password')])) {

                if (! $this->userTypeMatches(Auth::user()->user_type, $request->user_type)) {
                    Auth::logout();
                    return $this->failed_response(TranslationHelper::translate('account type not match'), 422);
                }

                $user =Auth::user()->createToken('MyApp')->accessToken;
                $user_data=Auth::user();
                $user_data['token'] = $user;
                $data = new UserResource(Auth::user());
                return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
        }
        return $this->failed_response(TranslationHelper::translate('phone or password not correct'));

    }

    private function userTypeMatches(?string $actualType, ?string $requestedType): bool
    {
        if ($actualType === $requestedType) {
            return true;
        }

        return $actualType === 'buyer_vendor' && in_array($requestedType, ['buyer', 'vendor'], true);
    }
}
