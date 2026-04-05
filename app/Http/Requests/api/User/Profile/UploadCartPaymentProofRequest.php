<?php

namespace App\Http\Requests\api\User\Profile;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadCartPaymentProofRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'proof' => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'proof.required' => TranslationHelper::translate('Please choose an image or PDF for payment proof'),
            'proof.file' => TranslationHelper::translate('Invalid file upload'),
            'proof.mimes' => TranslationHelper::translate('Payment proof must be jpeg, png, webp or pdf'),
            'proof.max' => TranslationHelper::translate('Payment proof max size 10 MB'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success' => false,
            'message' => $validator->errors()->first(),
        ]));
    }
}
