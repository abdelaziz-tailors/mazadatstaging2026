<?php

namespace App\Http\Requests\Dashboard\Partner;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {


        $admin = Admin::findorfail(request()->route('partner'));

        return [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,'.request()->route('partner').',id,deleted_at,NULL',
            'user_name' => 'required|string|unique:users,user_name,'.$admin->user_id,
            'phone' => 'required',
            'image' => 'sometimes|required|mimes:png,jpg,jpeg',
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
            'role_id.required' => TranslationHelper::translate('Please Choose Partner Role'),
            'image.required' => TranslationHelper::translate('Please Choose Image'),
            'image.mimes' => TranslationHelper::translate('Please Choose Valid Image')
        ];
    }
}
