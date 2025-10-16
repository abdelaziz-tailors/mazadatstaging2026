<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ProfileCompleteRequest;
use App\Http\Requests\api\User\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Patient;
use App\Models\User\User;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;


class ProfileCompleteController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(ProfileCompleteRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $patients = auth('api')->user();
        $patients->update([
            'email' => $request->email,
            'phone' => $request->phone,
            'user_name' => $request->user_name,
            'birth_date' => $request->birth_date,
        ]);
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), '');
    }


}
