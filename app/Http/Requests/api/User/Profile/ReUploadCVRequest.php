<?php

namespace App\Http\Requests\api\User\Profile;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReUploadCVRequest extends FormRequest
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
            'cv'=>['required','mimes:jpg,jpeg,png,pdf,PDF|max:2048'],
        ];

    }

    public function messages()
    {
        return [
            'cv.required' => TranslationHelper::translate('Please Upload Your CV'),
            'cv.mimes' => TranslationHelper::translate('Please Upload Valid  CV'),
            'cv.max' => TranslationHelper::translate(' CV Max size 2MB'),
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
