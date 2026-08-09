<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class LoginRequest extends FormRequest
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
            'phone' => 'nullable|required_without:email',
            'email' => 'nullable|email|required_without:phone',
            'password' => 'required',
            'user_type' => 'required|in:buyer,vendor,buyer_vendor,seller',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => TranslationHelper::translate('please Enter phone'),
            'password.required' => TranslationHelper::translate('please_enter_password'),
            'user_type.required' => TranslationHelper::translate('please enter user type'),
            'user_type.in' => TranslationHelper::translate('please enter user  type (buyer, vendor, buyer_vendor, seller)'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'success'   => false,
            'message'   => $validator->errors()->first()
        ]));
    }
}
