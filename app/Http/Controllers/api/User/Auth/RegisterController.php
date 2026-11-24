<?php

namespace App\Http\Controllers\api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Auth\RegisterRequest;
use App\Helpers\TranslationHelper;
use App\Models\UserOtp;
use App\Models\User\User;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;

class RegisterController extends Controller
{
    use HelperTrait, ResponseTrait;

    public function __invoke(RegisterRequest $request)
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $otpService = app(\App\Services\OtpService::class);
                $otpType = 'register';
                $challenge = $otpService->registrationChallenge();
                $numbers = $challenge['otp'];
                $expire_at = $challenge['expire_at'];
                $challengeId = bin2hex(random_bytes(32));

                $commercialRegisterName = null;
                if (($request->user_type ?? 'buyer') === 'vendor' && $request->hasFile('commercial_register')) {
                    $file = $request->file('commercial_register');
                    $commercialRegisterName = 'vendor-commercial-files/'. mt_rand(11111, 99999).'_'.$file->getClientOriginalName();
                    $file->move(public_path('../storage/app/public/vendor-commercial-files'), $commercialRegisterName);
                }

                $pendingRegistration = [
                    'type' => $otpType,
                    'is_verified' => false,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'user_name' => $request->user_name,
                    'national_id' => $request->input('national_id'),
                    'user_type' => $request->user_type ?? 'buyer',
                    'city_id' => $request->city,
                    'commercial_register' => $commercialRegisterName,
                    'password' => bcrypt($request->password),
                    'otp' => $numbers,
                    'expire_at' => $expire_at,
                    'registration_challenge_hash' => hash('sha256', $challengeId),
                ];

                $otpRecords = \App\Helpers\MobileNumber::matching(UserOtp::withTrashed(), $request->phone)
                    ->where('type', $otpType)->where('user_type', $request->input('user_type') ?: 'buyer')->lockForUpdate()->limit(2)->get();
                if ($otpRecords->count() > 1) {
                    throw new \RuntimeException('Ambiguous pending registration');
                }
                $otpRecord = $otpRecords->first();

                if ($otpRecord) {
                    $otpRecord->update(array_merge($pendingRegistration, [
                        'deleted_at' => null,
                    ]));
                } else {
                    $otpRecord = UserOtp::create($pendingRegistration);
                }

                return $this->success_response(TranslationHelper::translate('new_otp_has_been_sent'), [
                    'phone' => $request->phone,
                    'challenge_id' => $challengeId,
                    'expire_at' => $expire_at,
                ]);

            });
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException && \App\Exceptions\Handler::identityConflict($th)) {
                return app(\App\Exceptions\Handler::class)->render($request, $th);
            }
            $this->logRegistrationFailure('REGISTER_FAILED', $th);
            return $this->failed_response(TranslationHelper::translate('Something went wrong'), 500);
        }
    }

    private function logRegistrationFailure(string $event, \Throwable $exception): void
    {
        // Exception messages, SQL bindings and traces may contain passwords or OTPs.
        \Log::error($event, [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'sqlstate' => $exception instanceof \Illuminate\Database\QueryException
                ? ($exception->errorInfo[0] ?? null) : null,
        ]);
    }
    public function UserCheckData(RegisterRequest $request)
    {


        return $this->success_response(TranslationHelper::translate('successfully'), '');
    }

}
