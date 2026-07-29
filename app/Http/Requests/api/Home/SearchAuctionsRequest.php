<?php

namespace App\Http\Requests\api\Home;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class SearchAuctionsRequest extends FormRequest
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
            'q' => 'required|string|min:1',
        ];
    }

    public function messages()
    {
        return [
            'q.required' => TranslationHelper::translate('please enter a search keyword'),
            'q.string' => TranslationHelper::translate('please enter a search keyword'),
            'q.min' => TranslationHelper::translate('please enter a search keyword'),
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
