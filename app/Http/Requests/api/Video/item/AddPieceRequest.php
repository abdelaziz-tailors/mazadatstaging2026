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
            'weight' => 'nullable|numeric',
            'identifier' => 'nullable|string|max:255',
            'baham_count' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'weight.numeric' => TranslationHelper::translate('weight must be a number.'),
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
        ], 422));
    }
}
