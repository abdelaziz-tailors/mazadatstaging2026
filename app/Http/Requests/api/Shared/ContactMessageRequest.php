<?php

namespace App\Http\Requests\api\Shared;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => TranslationHelper::translate('Please enter your full name'),
            'email.required' => TranslationHelper::translate('Please enter your email'),
            'email.email' => TranslationHelper::translate('Please enter a valid email address'),
            'subject.required' => TranslationHelper::translate('Please enter a subject'),
            'message.required' => TranslationHelper::translate('Please enter your message'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success' => false,
            'message' => $validator->errors()->first(),
        ]));
    }
}
