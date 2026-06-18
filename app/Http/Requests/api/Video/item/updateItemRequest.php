<?php

namespace App\Http\Requests\api\Video\item;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class updateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Set to true for now; adjust based on your auth logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Ensure each item in the 'title' array is required, a string, and max 255 chars
            'lineage_title_ar' => 'sometimes',
            'lineage_title' => 'sometimes',
            'address' => 'sometimes',
            'terms' => 'sometimes',
            'terms_ar' => 'sometimes',
            'type' => 'sometimes',
            'information' => 'sometimes',
            'information_ar' => 'sometimes',
            'category_id' => 'sometimes|integer|exists:categories,id', // Assuming category_id references a table
            'partner_id' => 'nullable|integer|exists:users,id', // vendor partner
            'seller_id' => 'nullable|integer|exists:users,id', //  seller
            'weight' => 'sometimes|numeric', // Assuming weight should be a number
            'age' => 'sometimes|integer', // Assuming age is an integer
            'age_type' => 'sometimes', // Assuming age is an integer
            // 'start_price' => 'sometimes|numeric', // Assuming price is a number
            'bidding' => 'sometimes|numeric',
            'quantity' => 'nullable',
            'quantity.*' => 'numeric',
            'piece_multiplier_number' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'baham_count' => 'nullable|string|max:255',
            'color_id' => 'sometimes|integer|exists:colors,id', // Assuming category_id references a table
            'video'=>'nullable|file|mimes:mp4,mov,avi,wmv,flv|max:20480'
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'lineage_title_ar.required' => TranslationHelper::translate('lineage_title_ar field is required.'),
            'lineage_title.required' => TranslationHelper::translate('lineage_title field is required.'),
            'address.required' => TranslationHelper::translate('address field is required.'),
            'information.required' => TranslationHelper::translate('information field is required.'),
            'information_ar.required' => TranslationHelper::translate('information_ar field is required.'),
            'category_id.required' => TranslationHelper::translate('category_id field is required.'),
            'category_id.integer' => TranslationHelper::translate('category_id must be a number.'),
            'category_id.exists' => TranslationHelper::translate('category_id must exist in the system.'),
            'partner_id.integer' => TranslationHelper::translate('partner_id must be a number.'),
            'partner_id.exists' => TranslationHelper::translate('The selected partner does not exist in the system.'),
            'weight.required' => TranslationHelper::translate('weight field is required.'),
            'weight.numeric' => TranslationHelper::translate('weight must be a number.'),
            'age.required' => TranslationHelper::translate('age field is required.'),
            'age.integer' => TranslationHelper::translate('age must be a number.'),
            'start_price.required' => TranslationHelper::translate('start price field is required.'),
            'start_price.numeric' => TranslationHelper::translate('start price must be a number.'),
            'bidding.required' => TranslationHelper::translate('bidding field is required.'),
            'bidding.boolean' => TranslationHelper::translate('bidding must be true or false.'),
            'quantity.*.required' => TranslationHelper::translate('Each quantity field is required.'),
            'quantity.*.numeric' => TranslationHelper::translate('Each quantity must be a number.'),
            'image.*.required' => TranslationHelper::translate('image is required.'),
            'image.*.image' => TranslationHelper::translate('file must be an image.'),
            'image.*.mimes' => TranslationHelper::translate('Only JPEG, JPG, and PNG formats are allowed.'),
            'image.*.max' => TranslationHelper::translate('image must not exceed 20MB.'),
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
            'code' => 422, // Use 422 for validation errors, not 200
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
