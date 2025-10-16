<?php

namespace App\Http\Requests\Dashboard\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;

class UpdateAdminRequest extends FormRequest
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
            'email' => 'required|email|unique:admins,email,'.request()->route('admin').',id,deleted_at,NULL',
            'phone' => 'required',
            'role_id' => 'required',
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
            'name.required' => TranslationHelper::translate('Please Enter Administrator name'),
            'email.required' => TranslationHelper::translate('Please Enter Administrator E-mail Address'),
            'email.email' => TranslationHelper::translate('Please Enter Valid Administrator E-mail Address'),
            'email.unique' => TranslationHelper::translate('E-mail Address Registered For Another Admin'),
            'phone.required' => TranslationHelper::translate('Please Enter Administrator Phone'),
            'role_id.required' => TranslationHelper::translate('Please Choose Administrator Role'),
            'image.required' => TranslationHelper::translate('Please Choose Image'),
            'image.mimes' => TranslationHelper::translate('Please Choose Valid Image')
        ];
    }
}
