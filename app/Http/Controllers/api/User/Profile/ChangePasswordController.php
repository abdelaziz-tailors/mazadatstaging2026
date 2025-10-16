<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\ChangePasswordRequest;
use App\Helpers\TranslationHelper;

use Illuminate\Support\Facades\Auth;

use App\Http\Resources\UserResource;

use App\Traits\ResponseTrait;

class ChangePasswordController extends Controller
{
    use ResponseTrait;

    public function __invoke(ChangePasswordRequest $request) {
        $user = Auth::user();
        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return $this->success_response(TranslationHelper::translate('your_password_changed_successfully'), new UserResource($user));
    }
}
