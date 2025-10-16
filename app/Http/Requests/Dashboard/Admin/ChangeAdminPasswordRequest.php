<?php

namespace App\Http\Requests\Dashboard\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;

class ChangeAdminPasswordRequest extends FormRequest
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
            'password' => 'required|min:6|max:8|confirmed'
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
            'password.required' => TranslationHelper::translate('Please Enter Password'),
            'password.confirmed' => TranslationHelper::translate('Password And Its Confirmation Not Matching')
        ];
    }
}
