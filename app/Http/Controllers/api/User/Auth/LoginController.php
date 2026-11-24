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

        $identifier = $request->input('email') ?: $request->input('phone');
        $loginField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $matches = $loginField === 'phone'
            ? \App\Helpers\MobileNumber::matching(User::where('user_type', $request->user_type), (string) $identifier)->limit(2)->get()
            : User::where('user_type', $request->user_type)->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower(trim($identifier), 'UTF-8')])->limit(2)->get();

        if ($matches->count() === 1 && Auth::attempt(['id' => $matches->first()->id, 'password' => $request->password])) {

                if (! Auth::user()->is_active || ! Auth::user()->is_verified || Auth::user()->user_type !== $request->user_type) {
                    Auth::logout();
                    return $this->failed_response(TranslationHelper::translate('account type not match'), 422);
                }

                Auth::user()->setAttribute('active_role', $request->user_type);
                $user =Auth::user()->createToken('MyApp')->accessToken;
                $user_data=Auth::user();
                $user_data['token'] = $user;
                $data = new UserResource(Auth::user());
                return $this->success_response(TranslationHelper::translate('your_account_logged_in_successfully'), $data);
        }
        return $this->failed_response(TranslationHelper::translate(
            $loginField === 'email' ? 'email or password not correct' : 'phone or password not correct'
        ));

    }

}
