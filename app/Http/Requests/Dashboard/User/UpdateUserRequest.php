<?php

namespace App\Http\Requests\Dashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use App\Helpers\MobileNumber;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the User is authorized to make this request.
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
        MobileNumber::prepare($this);
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'email' => MobileNumber::emailRules($this->route('user')),
            'phone' => MobileNumber::rules($this->route('user')),
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
            'name.required' => TranslationHelper::translate('Please Enter User name'),
            'email.required' => TranslationHelper::translate('Please Enter User E-mail Address'),
            'email.email' => TranslationHelper::translate('Please Enter Valid User E-mail Address'),
            'email.unique' => TranslationHelper::translate('E-mail Address Registered For Another User'),
            'phone.required' => TranslationHelper::translate('Please Enter User Phone'),
            'phone.unique' => TranslationHelper::translate('Phone Registered For Another User'),

        ];
    }
}
