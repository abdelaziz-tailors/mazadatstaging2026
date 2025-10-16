<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class SocailLoginRequest extends FormRequest
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
            'email' => 'required|email',
            'google_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => TranslationHelper::translate('please_enter_full_name'),
            'email.required' => TranslationHelper::translate('please_enter_email_address'),
            'email.email' => TranslationHelper::translate('please_enter_valid_email'),
            'google_id.required' => TranslationHelper::translate('please_enter_google_id'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success'   => false,
            'message'   => $validator->errors()->first()
        ]));
    }
}
