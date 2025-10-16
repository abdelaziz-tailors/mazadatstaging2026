<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class SocialLoginRequest extends FormRequest
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
//            'website' => 'required|in:google,facebook,apple',
//            'user_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'website.required' => TranslationHelper::translate('please_choose_website'),
            'website.in' => TranslationHelper::translate('please_choose_website_from'),
            'user_id.required' => TranslationHelper::translate('please_enter_website_user_id'),
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
