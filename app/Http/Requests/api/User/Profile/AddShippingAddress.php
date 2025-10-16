<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
class AddShippingAddress extends FormRequest
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
            'id' => 'required',
            'shipping_address' => 'required',
            'city_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => TranslationHelper::translate('Please Enter Item Id'),
            'shipping_address.required' => TranslationHelper::translate('Please Enter Shipping Address'),
            'city_id.required' => TranslationHelper::translate('Please Enter City Id'),
            'lat.required' => TranslationHelper::translate('Please Enter Lat'),
            'lng.required' => TranslationHelper::translate('Please Enter Lng'),
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
