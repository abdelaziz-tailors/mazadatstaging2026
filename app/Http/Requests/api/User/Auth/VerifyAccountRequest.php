<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class VerifyAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function prepareForValidation()
    {
        $phone = $this->input('phone');
        if (is_string($phone)) {
            $pending = \App\Helpers\MobileNumber::matching(\App\Models\UserOtp::query(), $phone)
                ->where('type', 'register')->where('is_verified', false)->limit(2)->get();
            $user = \App\Helpers\MobileNumber::find(\App\Models\User\User::class, $phone);
            $this->merge(['phone' => $pending->count() === 1 ? $pending->first()->phone
                : ($user ? $user->phone : $phone)]);
        }
    }

    public function rules()
    {
        return [
            'phone' => 'required',
            'otp' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => TranslationHelper::translate('please Enter phone'),
            'otp.required' => TranslationHelper::translate('please_enter_otp'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success'   => false,
            'message'   => $validator->errors()->first()
        ]));
    }
}
