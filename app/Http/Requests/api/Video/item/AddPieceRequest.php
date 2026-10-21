<?php

namespace App\Http\Requests\api\Video\item;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddPieceRequest extends FormRequest
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
            'age' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|gt:0',
            'identifier' => 'nullable|string|max:255',
            'baham_count' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'weight.numeric' => trans('validation.head_weight_numeric'),
            'weight.gt' => trans('validation.head_weight_positive'),
        ];
    }

    /**
     * Handle failed validation by returning a JSON response.
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }
}
