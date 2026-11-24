<?php

namespace App\Http\Requests\api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class VerifyAccountRequest extends FormRequest
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
    protected function prepareForValidation()
    {
        // Resolve only the supplied challenge hash and canonical phone in the controller.
    }

    public function rules()
    {
        return [
            'phone' => 'required|string',
            'user_type' => 'nullable|string|in:buyer,vendor,buyer_vendor,seller',
            'otp' => 'required',
            'challenge_id' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/D'],
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => TranslationHelper::translate('please Enter phone'),
            'otp.required' => TranslationHelper::translate('please_enter_otp'),
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
