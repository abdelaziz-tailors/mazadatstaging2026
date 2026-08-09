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

    protected function prepareForValidation()
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone_local', $this->input('phone')));
        if (str_starts_with($phone, '00966')) $phone = substr($phone, 5);
        elseif (str_starts_with($phone, '966')) $phone = substr($phone, 3);
        if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
        $this->merge(['phone' => $phone ? '+966'.$phone : $phone]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $admin = Auth::guard('admin')->user();
        $userNameUniqueRule = $admin->user_id
            ? 'unique:users,user_name,'.$admin->user_id
            : 'unique:admins,user_name,'.$admin->id.',id,deleted_at,NULL';

        return [
            'name' => 'required',
            'user_name' => ['nullable', 'string', $userNameUniqueRule],
            'email' => 'required|email|unique:admins,email,'.$admin->id.',id,deleted_at,NULL',
            'phone' => ['required', 'regex:/^\+9665[0-9]{8}$/'],
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
            'phone.required' => 'يرجى إدخال رقم جوال سعودي صحيح',
            'phone.regex' => 'يرجى إدخال رقم جوال سعودي صحيح',
            'image.required' => TranslationHelper::translate('Please Choose Image'),
            'image.mimes' => TranslationHelper::translate('Please Choose Valid Image'),
            'birth_date.before_or_equals' => TranslationHelper::translate('max birthdate should be before 01-01-2022'),
        ];
    }
}
