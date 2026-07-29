<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class RegisterRequest extends FormRequest
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
            'name' => 'required|min:3',
//            'name' => 'required|regex:/^[\pL\s]+$/u|min:3',
            'phone' => 'nullable|numeric|unique:users,phone,NULL,id,deleted_at,NULL',
            'user_name' => 'required|string|unique:users,user_name,NULL,id,deleted_at,NULL',
//            'user_name' => 'required|min:3',

            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            // 'account_type' => 'required',
            'user_type' => 'nullable|in:buyer,vendor,buyer_vendor,seller',
            'commercial_register' => 'exclude_unless:user_type,vendor|required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'password' => 'required|min:6|confirmed',
            'city' => 'nullable|exists:cities,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => TranslationHelper::translate('please_enter_full_name'),
            'account_type.required' => TranslationHelper::translate('please enter account type'),
            'user_type.required' => TranslationHelper::translate('please enter user type'),
            'user_type.in' => TranslationHelper::translate('please enter user  type (buyer, vendor, buyer_vendor, seller)'),
            'commercial_register.required' => TranslationHelper::translate('commercial_register_required_for_vendor'),
            'commercial_register.file' => TranslationHelper::translate('please_add_valid_commercial_register'),
            'commercial_register.mimes' => TranslationHelper::translate('please_add_valid_commercial_register'),
            'commercial_register.max' => TranslationHelper::translate('_commercial_register_max_size_10_mb_'),
            'name.regex' => TranslationHelper::translate('Name Must to be alphabetic'),
            'name.min' => TranslationHelper::translate('Name min 3 liter'),
            'email.required' => TranslationHelper::translate('please_enter_email_address'),
            'email.email' => TranslationHelper::translate('please_enter_valid_email'),
            'email.unique' => TranslationHelper::translate('email_address_exists'),
            'phone.required' => TranslationHelper::translate('please_enter_phone'),
            'phone.numeric' => TranslationHelper::translate('phone must be a number'),
            'phone.unique' => TranslationHelper::translate('phone_number_exists'),
            'phone.max' => TranslationHelper::translate('phone should have at least 11 number'),
            'phone.min' => TranslationHelper::translate('phone should have at least 11 number'),
            'user_name.required' => TranslationHelper::translate('Please Enter User Name'),
            'user_name.unique' => TranslationHelper::translate('User Name Exists'),
            'user_name.string' => TranslationHelper::translate('User Name Must to be string'),
            'user_name.regex' => TranslationHelper::translate('please Enter User Name without spaces '),
            'user_name.max' => TranslationHelper::translate('Name must consist of a maximum of 20 characters '),
            'birth_date.required' => TranslationHelper::translate('Please Enter Date Of Birth'),
            'birth_date.date_format' => TranslationHelper::translate('please Enter Valid Date'),
            'password.required' => TranslationHelper::translate('please_enter_password'),
            'passsword.min' => TranslationHelper::translate('password should have at least 6 characters'),
            'passsword.max' => TranslationHelper::translate('The password must consist of a maximum of 8 characters'),
            'city.exists' => TranslationHelper::translate('select_city'),

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
