<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class ProfileCompleteRequest extends FormRequest
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
            'user_name' => 'required|string|regex:/^\S*$/u|max:20|unique:users,user_name,'.auth('api')->user()->id.',id,deleted_at,NULL',
            'email' => 'required|email|unique:users,email,'.auth('api')->user()->id.',id,deleted_at,NULL',
            'phone' => 'required|unique:users,phone,'.auth('api')->user()->id.',id,deleted_at,NULL',
            'birth_date' => 'required|date_format:Y-m-d',
            'birth_date.required' => TranslationHelper::translate('Please Enter Date Of Birth'),
            'birth_date.date_format' => TranslationHelper::translate('please Enter Valid Date'),

        ];
    }

    public function messages()
    {
        return [
            'user_name.required' => TranslationHelper::translate('Please Enter User Name'),
            'user_name.unique' => TranslationHelper::translate('User Name Exists'),
            'user_name.string' => TranslationHelper::translate('User Name Must to be string'),
            'user_name.regex' => TranslationHelper::translate('please Enter User Name without spaces '),
            'user_name.max' => TranslationHelper::translate('Name must consist of a maximum of 20 characters '),
            'phone.required' => TranslationHelper::translate('please_enter_phone'),
            'phone.unique' => TranslationHelper::translate('phone_number_exists'),
            'name.required' => TranslationHelper::translate('please_enter_full_name'),
            'email.required' => TranslationHelper::translate('please_enter_email_address'),
            'email.email' => TranslationHelper::translate('please_enter_valid_email'),
            'email.unique' => TranslationHelper::translate('email_address_exists'),

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
