<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class ResetPasswordRequest extends FormRequest
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
    public function rules()
    {
        return [
            'phone' => 'required',
            'otp' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => TranslationHelper::translate('please Enter phone'),
            'otp.required' => TranslationHelper::translate('please_enter_otp'),
            'password.required' => TranslationHelper::translate('please_enter_password'),
            'password.min' => TranslationHelper::translate('password_should_have_at_least_6_characters'),
            'password.confirmed' => TranslationHelper::translate('password_confirmation_not_matching'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'success'   => false,
            'message'   => $validator->errors()->first()
        ], 422));
    }
}
