<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class SearchTypeRequest extends FormRequest
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
            'type' => 'required',
            'lat' => 'nullable|required_if:type,location',
            'lng' => 'nullable|required_if:type,location',
            'city_id' => 'nullable|required_if:type,city',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => TranslationHelper::translate('please choose search type'),
            'lat.required' => TranslationHelper::translate('please choose search lat'),
            'lng.required' => TranslationHelper::translate('please choose search lng'),
            'city_id.required' => TranslationHelper::translate('please choose search city_id'),

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
