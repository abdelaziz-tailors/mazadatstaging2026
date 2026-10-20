<?php

namespace App\Http\Requests\api\Video\item;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
            'weight' => [
                'filled',
                Rule::requiredIf(function () {
                    return ! $this->filled('pieces') && $this->hasAny(['age', 'weight', 'identifier', 'age_type']);
                }),
                'numeric', 'gt:0',
            ],
            'age' => 'sometimes|integer', // Assuming age is an integer
            'age_type' => 'sometimes', // Assuming age is an integer
            // 'start_price' => 'sometimes|numeric', // Assuming price is a number
            'bidding' => 'sometimes|numeric',
            'quantity' => 'nullable',
            'quantity.*' => 'numeric',
            'pieces' => 'nullable|array',
            'pieces.*' => 'required|array',
            'image' => 'sometimes|array|min:1',
            'image.*' => 'required|file|image|mimes:jpeg,jpg,png|max:20000',
            'pieces.*.age' => 'nullable|string|max:255',
            'pieces.*.weight' => 'required|numeric|gt:0',
            'pieces.*.piece_multiplier_number' => 'nullable|string|max:255',
            'pieces.*.identifier' => 'nullable|string|max:255',
            'pieces.*.baham_count' => 'nullable|string|max:255',
            'piece_multiplier_number' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'baham_count' => 'nullable|string|max:255',
            'color_id' => 'sometimes|integer|exists:colors,id', // Assuming category_id references a table
            'video'=>'nullable|file|mimes:mp4,mov,avi,wmv,flv|max:20480'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if (! $this->has('quantity') && ! $this->has('pieces')) {
                return;
            }

            $quantity = (int) $this->input('quantity', 0);
            $pieces = $this->input('pieces', []) ?? [];

            if ($quantity > 1) {
                if (count($pieces) !== $quantity) {
                    $validator->errors()->add(
                        'pieces',
                        TranslationHelper::translate('pieces_count_must_match_quantity.')
                    );
                }

                foreach ($pieces as $pieceIndex => $piece) {
                    if (blank($piece['age'] ?? null)) {
                        $validator->errors()->add(
                            "pieces.$pieceIndex.age",
                            TranslationHelper::translate('Each age field is required.')
                        );
                    }
                }
            }
        });
    }

    /**
     * Custom validation error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'weight.required' => trans('validation.head_weight_required'),
            'weight.filled' => trans('validation.head_weight_required'),
            'weight.numeric' => trans('validation.head_weight_numeric'),
            'weight.gt' => trans('validation.head_weight_positive'),
            'pieces.*.weight.required' => trans('validation.head_weight_required'),
            'pieces.*.weight.numeric' => trans('validation.head_weight_numeric'),
            'pieces.*.weight.gt' => trans('validation.head_weight_positive'),
            'image.required' => trans('validation.animal_images_required'),
            'image.min' => trans('validation.animal_images_required'),
            'image.array' => trans('validation.animal_images_array'),
            'image.*.required' => trans('validation.animal_image_file'),
            'image.*.file' => trans('validation.animal_image_file'),
            'image.*.image' => trans('validation.animal_image_file'),
            'image.*.mimes' => trans('validation.animal_image_file'),
            'image.*.max' => trans('validation.animal_image_max'),
            'image.*.uploaded' => trans('validation.animal_image_uploaded'),
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
            'age.required' => TranslationHelper::translate('age field is required.'),
            'age.integer' => TranslationHelper::translate('age must be a number.'),
            'start_price.required' => TranslationHelper::translate('start price field is required.'),
            'start_price.numeric' => TranslationHelper::translate('start price must be a number.'),
            'bidding.required' => TranslationHelper::translate('bidding field is required.'),
            'bidding.boolean' => TranslationHelper::translate('bidding must be true or false.'),
            'quantity.*.required' => TranslationHelper::translate('Each quantity field is required.'),
            'quantity.*.numeric' => TranslationHelper::translate('Each quantity must be a number.'),
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
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }
}
