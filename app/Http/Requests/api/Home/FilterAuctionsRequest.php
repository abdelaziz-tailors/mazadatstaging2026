<?php

namespace App\Http\Requests\api\Home;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class FilterAuctionsRequest extends FormRequest
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
            'status' => 'required|in:inprogress,upcoming,archive',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => TranslationHelper::translate('please choose a status'),
            'status.in' => TranslationHelper::translate('please choose a valid status (inprogress, upcoming, archive)'),
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
