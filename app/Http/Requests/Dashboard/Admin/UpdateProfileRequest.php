<?php

namespace App\Http\Requests\Dashboard\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Helpers\TranslationHelper;

class UpdateProfileRequest extends FormRequest
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
            'email' => 'required|email|unique:admins,email,'.Auth::guard('admin')->user()->id.',id,deleted_at,NULL',
            'phone' => 'required',
            'image' => 'sometimes|required|mimes:png,jpg,jpeg',
            'experience_years'=> 'min:0',
            'birth_date'  => 'date|before:'.date('m/d/Y',strtotime('02/01/2022'))
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
            'name.required' => TranslationHelper::translate('Please Enter name'),
            'email.required' => TranslationHelper::translate('Please Enter E-mail Address'),
            'email.email' => TranslationHelper::translate('Please Enter Valid E-mail Address'),
            'email.unique' => TranslationHelper::translate('E-mail Address Registered For Another User'),
            'phone.required' => TranslationHelper::translate('Please Enter Phone'),
            'image.required' => TranslationHelper::translate('Please Choose Image'),
            'image.mimes' => TranslationHelper::translate('Please Choose Valid Image'),
            'birth_date.before_or_equals' => TranslationHelper::translate('max birthdate should be before 01-01-2022'),
        ];
    }
}
