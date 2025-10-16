<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class UpdateProfileFileRequest extends FormRequest
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
            'tax_certificate' => 'sometimes|mimes:jpeg,jpg,png|max:10240',
            'license' => 'sometimes|mimes:jpeg,jpg,png|max:10240',
            'commercial_register' => 'sometimes|mimes:jpeg,jpg,png|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'tax_certificate.mimes' => TranslationHelper::translate('Please Add Valid Tax Certificate'),
            'tax_certificate.max' => TranslationHelper::translate(' Tax Certificate max Size 10 MB '),
            'license.mimes' => TranslationHelper::translate('Please Add Valid License'),
            'license.max' => TranslationHelper::translate(' License max Size 10 MB '),
            'commercial_register.mimes' => TranslationHelper::translate('Please Add Valid Commercial Register'),
            'commercial_register.max' => TranslationHelper::translate(' Commercial Register max Size 10 MB '),
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
