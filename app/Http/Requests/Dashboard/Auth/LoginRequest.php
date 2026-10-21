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
    protected function prepareForValidation()
    {
        $identifier = $this->input('email');
        if (is_string($identifier) && ! filter_var($identifier, FILTER_VALIDATE_EMAIL)
            && \App\Helpers\MobileNumber::valid($identifier)) {
            $this->merge(['phone' => $identifier, 'email' => null]);
        }
    }

    public function rules()
    {
        return [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|string|max:32|required_without:email',
            'password'=>'required|string'
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
