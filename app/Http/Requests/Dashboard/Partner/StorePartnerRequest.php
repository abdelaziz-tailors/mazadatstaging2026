<?php

namespace App\Http\Requests\Dashboard\Partner;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;

class StorePartnerRequest extends FormRequest
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
     * The form only collects the 9-digit local mobile number (matching the
     * "5X XXX XXXX" placeholder next to a fixed "+966" badge in the UI) —
     * normalize it to the same "+966XXXXXXXXX" format already used by every
     * other phone value in the users/admins tables before validating. Same
     * convention as App\Http\Requests\Dashboard\Vendor\StoreVendorRequest.
     */
    protected function prepareForValidation()
    {
        if ($this->filled('phone')) {
            $digits = preg_replace('/\D/', '', $this->phone);
            $digits = ltrim($digits, '0');

            $this->merge(['phone' => '+966'.$digits]);
        }
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
            'email' => 'required|email|unique:admins,email,NULL,id,deleted_at,NULL',
            'user_name' => 'required|string|unique:users,user_name,NULL,id,deleted_at,NULL',
            'phone' => ['required', 'regex:/^\+9665[0-9]{8}$/'],
            'commercial_register' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'national_id' => 'nullable|string|max:32',
            'password' => 'required|min:6',
            'image' => 'nullable|mimes:png,jpg,jpeg'
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
            'name.required' => TranslationHelper::translate('Please Enter Partner name'),
            'email.required' => TranslationHelper::translate('Please Enter Partner E-mail Address'),
            'email.email' => TranslationHelper::translate('Please Enter Valid Partner E-mail Address'),
            'email.unique' => TranslationHelper::translate('E-mail Address Registered For Another Admin'),
            'phone.required' => TranslationHelper::translate('Please Enter Partner Phone'),
            'phone.regex' => TranslationHelper::translate('please_enter_valid_phone_with_country_code'),
            'role_id.required' => TranslationHelper::translate('Please Choose Partner Role'),
            'password.required' => TranslationHelper::translate('Please Enter Partner Account Password'),
            'password.min' => TranslationHelper::translate('Please Enter Partner Account Password'),
            'commercial_register.required' => TranslationHelper::translate('commercial_register_required_for_vendor'),
            'commercial_register.mimes' => TranslationHelper::translate('please_add_valid_commercial_register'),
            'commercial_register.max' => TranslationHelper::translate('_commercial_register_max_size_10_mb_'),
            'image.required' => TranslationHelper::translate('Please Choose Image'),
            'image.mimes' => TranslationHelper::translate('Please Choose Valid Image'),
        ];
    }
}
