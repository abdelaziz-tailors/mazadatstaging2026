<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class SubscriptionRequest extends FormRequest
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
            'transaction_image' => 'required|mimes:jpeg,jpg,png|max:10240',
            'package_id' => 'required|exists:packages,id',

        ];
    }

    public function messages()
    {
        return [
            'transaction_image.required' => TranslationHelper::translate('Please choose Transaction Image'),
            'transaction_image.mimes' => TranslationHelper::translate('Please Add Valid Transaction Image'),
            'transaction_image.max' => TranslationHelper::translate(' Transaction Image max Size 10 MB '),
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
