<?php

namespace App\Http\Requests\Dashboard\Auth;

use Illuminate\Foundation\Http\FormRequest;
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
            'email'=>'required|email',
            'password'=>'required'
        ];
    }

     /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => TranslationHelper::translate('please_enter_email_address'),
            'email.email' => TranslationHelper::translate('please_enter_valid_email'),
            'password.required' => TranslationHelper::translate('please_enter_password'),
        ];
    }
}
