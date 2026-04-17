<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\ForgetPasswordrRequest;
use App\Http\Requests\api\User\Auth\RegisterRequest;
use App\Helpers\TranslationHelper;

use App\Http\Resources\User\UserResource;
use App\Mail\NewPasswordEmail;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\User\User;


use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(RegisterRequest $request)
    {

        DB::beginTransaction();
        try {
            $numbers =  mt_rand(1000, 9999);
            $expire_at = Carbon::now()->addMinutes(10);

            $commercialRegisterName = null;
            if (($request->user_type ?? 'buyer') === 'vendor' && $request->hasFile('commercial_register')) {
                $file = $request->file('commercial_register');
                $commercialRegisterName = 'vendor-commercial-files/'. mt_rand(11111, 99999).'_'.$file->getClientOriginalName();
                $file->move(public_path('../storage/app/public/vendor-commercial-files'), $commercialRegisterName);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_name' => $request->user_name,
                // 'account_type' => $request->account_type,
                'user_type' => $request->user_type ?? 'buyer',
                'commercial_register' => $commercialRegisterName,
                'password' => bcrypt($request->password),
                'otp' => $numbers,
                'expire_at' => $expire_at,
            ]);


            if ($request->user_type == 'vendor') {
                $admin = Admin::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'type' => 'partner',
                    'user_id' => $user->id,
                    'password' => bcrypt($request->password),
                ]);
            }
            $token = $user->createToken('MyApp')->accessToken;

            $user['token'] = $token;

            DB::commit();
            return $this->success_response(TranslationHelper::translate(' Account Registered Successfully '), new UserResource($user));

        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->failed_response(TranslationHelper::translate('Something went wrong'),);
        }
    }
    public function UserCheckData(RegisterRequest $request)
    {


        return $this->success_response(TranslationHelper::translate('successfully'), '');
    }

    public function forgetPassword(ForgetPasswordrRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            return $this->failed_response(TranslationHelper::translate('That mail not exist'),);
        }

        // reset password and send email
        $newPassword = round(99999, 100000);
        $user->password = bcrypt($newPassword);
        $user->save();

        $password = [
            'name' => $user->name,
            'password' => $newPassword,

        ];

        try {
            \Mail::to($request->email)->send(new NewPasswordEmail($password));
        } catch (\Exception $e) {
            //dd($e);
        }

        return $this->success_response(TranslationHelper::translate('New Password Send to Your Email'), '');
    }
}
