<?php

namespace App\Http\Requests\Dashboard\Partner;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use App\Helpers\MobileNumber;
use App\Models\Admin;

class UpdatePartnerRequest extends FormRequest
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
     * Same normalization as StorePartnerRequest — the shared partners._form
     * partial only collects the 9-digit local number next to a fixed
     * "+966" badge on both create and edit.
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


        $admin = Admin::findorfail(request()->route('partner'));

        return [
            'name' => 'required',
            'email' => array_merge(MobileNumber::emailRules($admin->user_id), MobileNumber::emailRules($admin->id, 'admins')),
            'user_name' => 'required|string|unique:users,user_name,'.$admin->user_id,
            'phone' => array_merge(MobileNumber::rules($admin->user_id), MobileNumber::rules($admin->id, 'admins')),
            'commercial_register' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'national_id' => 'nullable|string|max:32',
            'image' => 'nullable|mimes:png,jpg,jpeg',
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
            'commercial_register.mimes' => TranslationHelper::translate('please_add_valid_commercial_register'),
            'commercial_register.max' => TranslationHelper::translate('_commercial_register_max_size_10_mb_'),
            'role_id.required' => TranslationHelper::translate('Please Choose Partner Role'),
            'image.required' => TranslationHelper::translate('Please Choose Image'),
            'image.mimes' => TranslationHelper::translate('Please Choose Valid Image')
        ];
    }
}
