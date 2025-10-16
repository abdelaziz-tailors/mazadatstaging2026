<?php

namespace App\Http\Requests\Dashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;

class StoreUserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'phone' => 'required|unique:users,phone,NULL,id,deleted_at,NULL',
            'county' => 'required',
            'city' => 'required',
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
            'city.required' => TranslationHelper::translate('Please Enter User city'),
            'county.required' => TranslationHelper::translate('Please Enter User county'),

        ];
    }
}
