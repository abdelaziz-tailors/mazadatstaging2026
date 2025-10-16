<?php

namespace App\Http\Requests\Dashboard\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the Vendor is authorized to make this request.
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
            'email' => 'required|email|unique:users,email,'.request()->route('vendor').',id,deleted_at,NULL',
            'phone' => 'required|unique:users,phone,'.request()->route('vendor').',id,deleted_at,NULL',
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
            'name.required' => TranslationHelper::translate('Please Enter Vendor name'),
            'email.required' => TranslationHelper::translate('Please Enter Vendor E-mail Address'),
            'email.email' => TranslationHelper::translate('Please Enter Valid Vendor E-mail Address'),
            'email.unique' => TranslationHelper::translate('E-mail Address Registered For Another Vendor'),
            'phone.required' => TranslationHelper::translate('Please Enter Vendor Phone'),
            'phone.unique' => TranslationHelper::translate('Phone Registered For Another Vendor'),
            'city.required' => TranslationHelper::translate('Please Enter Vendor city'),
            'county.required' => TranslationHelper::translate('Please Enter Vendor county'),

        ];
    }
}
