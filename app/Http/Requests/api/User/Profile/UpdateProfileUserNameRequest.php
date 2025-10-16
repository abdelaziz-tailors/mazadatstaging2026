<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class UpdateProfileUserNameRequest extends FormRequest
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
            'user_name' => 'required|string|unique:users,user_name,'.auth('api')->user()->id
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
