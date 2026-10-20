<?php

namespace App\Http\Requests\Dashboard\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use App\Helpers\MobileNumber;

class StoreVendorRequest extends FormRequest
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
     * existing phone value in the users table before validating, so the
     * uniqueness check actually compares against real stored data instead
     * of silently never matching. Same convention as
     * App\Http\Requests\Dashboard\User\StoreUserRequest.
     */
    protected function prepareForValidation()
    {
        MobileNumber::prepare($this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name,NULL,id,deleted_at,NULL',
            'phone' => MobileNumber::rules(),
            'email' => MobileNumber::emailRules(),
            'password' => 'required|string|min:8',
            'city_id' => 'required|exists:cities,id',
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
            'user_name.required' => TranslationHelper::translate('please_enter_user_name'),
            'user_name.unique' => TranslationHelper::translate('user_name_already_taken'),
            'phone.required' => TranslationHelper::translate('Please Enter User Phone'),
            'phone.regex' => TranslationHelper::translate('please_enter_valid_phone_with_country_code'),
            'phone.unique' => TranslationHelper::translate('Phone Registered For Another User'),
            'email.required' => TranslationHelper::translate('Please Enter User E-mail Address'),
            'email.email' => TranslationHelper::translate('Please Enter Valid User E-mail Address'),
            'email.unique' => TranslationHelper::translate('E-mail Address Registered For Another User'),
            'password.required' => TranslationHelper::translate('please_enter_password'),
            'password.min' => TranslationHelper::translate('password_must_be_strong'),
            'city_id.required' => TranslationHelper::translate('select_city'),
            'city_id.exists' => TranslationHelper::translate('select_city'),
        ];
    }
}
