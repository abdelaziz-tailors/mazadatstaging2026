<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
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
    protected function prepareForValidation()
    {
        \App\Helpers\MobileNumber::prepare($this);
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'email' => \App\Helpers\MobileNumber::emailRules(auth('api')->id()),
            'phone' => \App\Helpers\MobileNumber::rules(auth('api')->id(), 'users', auth('api')->user()->user_type),

        ];
    }

    public function messages()
    {
        return [
            'name.required' => TranslationHelper::translate('please_enter_full_name'),
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
