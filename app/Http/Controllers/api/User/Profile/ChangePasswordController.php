<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\ChangePasswordRequest;
use App\Http\Resources\User\UserNoTokenResource;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    use ResponseTrait;

    public function __invoke(ChangePasswordRequest $request)
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $authenticated = auth('api')->user();
            if ($authenticated->credential_group_id) {
                \Illuminate\Support\Facades\DB::table('credential_groups')->where('id', $authenticated->credential_group_id)->lockForUpdate()->first();
            }
            $user = \App\Models\User\User::whereKey($authenticated->id)->lockForUpdate()->firstOrFail();

            if (! Hash::check($request->current_password, $user->password)) {
                return $this->failed_response(TranslationHelper::translate('Current password is incorrect'));
            }

            $user->update([
                'password' => bcrypt($request->password),
            ]);

            return $this->success_response(
                TranslationHelper::translate('your_password_changed_successfully'),
                new UserNoTokenResource($user->fresh())
            );
        }, 3);
    }
}
