<?php

namespace App\Http\Requests\api\Video\item;

use App\Helpers\TranslationHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreItemRequest extends FormRequest
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
            'lineage_title.*' => 'required',
            'lineage_title_ar.*' => 'required',
            'information' => 'required',
            'category_id'=>'required',
            'category_id.*' => 'required|integer|exists:categories,id', // Assuming category_id references a table
            'partner_id.*' => 'nullable|integer|exists:users,id', // Validate partner_id exists in users table
            'seller_id.*' => 'nullable|integer|exists:users,id',
            'weight' => 'nullable',
            'weight.*' => 'nullable|numeric',
            'age' => 'nullable',
            'age.*' => 'nullable|string',
            // 'start_price' => 'required', // Assuming price is a number
            // 'start_price.*' => 'numeric', // Assuming price is a number

            'bidding' => 'required',
            'bidding.*' => 'numeric',
            'image' => 'required', // Each image in a nested array
            'image.*.*' => 'required|image|mimes:jpeg,jpg,png|max:20000', // Each image in a nested array
            'video.*'=>'nullable|mimes:mp4,avi,wmv,flv|max:20000',
            'quantity' => 'nullable',
            'quantity.*' => 'numeric',
            'pieces' => 'nullable|array',
            'pieces.*' => 'nullable|array',
            'pieces.*.*.age' => 'nullable|string|max:255',
            'pieces.*.*.weight' => 'nullable|numeric',
            'pieces.*.*.piece_multiplier_number' => 'nullable|string|max:255',
            'pieces.*.*.identifier' => 'nullable|string|max:255',
            'pieces.*.*.baham_count' => 'nullable|string|max:255',
            'piece_multiplier_number' => 'nullable',
            'piece_multiplier_number.*' => 'nullable|string|max:255',
            'identifier' => 'nullable',
            'identifier.*' => 'nullable|string|max:255',
            'baham_count' => 'nullable',
            'baham_count.*' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $quantities = $this->input('quantity', []);

            foreach ($quantities as $index => $quantity) {
                $quantity = (int) $quantity;
                $pieces = $this->input("pieces.$index", []);

                if ($quantity > 1) {
                    if (count($pieces) !== $quantity) {
                        $validator->errors()->add(
                            "pieces.$index",
                            TranslationHelper::translate('pieces_count_must_match_quantity.')
                        );
                    }

                    foreach ($pieces as $pieceIndex => $piece) {
                        if (blank($piece['age'] ?? null)) {
                            $validator->errors()->add(
                                "pieces.$index.$pieceIndex.age",
                                TranslationHelper::translate('Each age field is required.')
                            );
                        }
                    }
                } elseif (blank($this->input("age.$index"))) {
                    $validator->errors()->add(
                        "age.$index",
                        TranslationHelper::translate('Each age field is required.')
                    );
                }

                if ($quantity <= 1 && blank($this->input("weight.$index"))) {
                    $validator->errors()->add(
                        "weight.$index",
                        TranslationHelper::translate('Each weight field is required.')
                    );
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
            'lineage_title.*.required' => TranslationHelper::translate('Each lineage title field is required.'),
            'lineage_title.*.string' => TranslationHelper::translate('Each lineage title must be a valid text string.'),
            'lineage_title.*.max' => TranslationHelper::translate('Each lineage title must not exceed 255 characters.'),
            'lineage_title_ar.*.required' => TranslationHelper::translate('Each lineage title ar field is required.'),
            'lineage_title_ar.*.string' => TranslationHelper::translate('Each lineage title ar must be a valid text string.'),
            'lineage_title_ar.*.max' => TranslationHelper::translate('Each lineage title ar must not exceed 255 characters.'),


            'information.*.required' => TranslationHelper::translate('Each information field is required.'),
            'information.*.string' => TranslationHelper::translate('Each information must be a valid text string.'),
            'information.*.max' => TranslationHelper::translate('Each information must not exceed 255 characters.'),
            'category_id.*.required' => TranslationHelper::translate('Each category_id field is required.'),
            'category_id.*.integer' => TranslationHelper::translate('Each category_id must be a number.'),
            'category_id.*.exists' => TranslationHelper::translate('Each category_id must exist in the system.'),
            'partner_id.*.integer' => TranslationHelper::translate('Each partner_id must be a number.'),
            'partner_id.*.exists' => TranslationHelper::translate('The selected partner does not exist in the system.'),
            'weight.*.required' => TranslationHelper::translate('Each weight field is required.'),
            'weight.*.numeric' => TranslationHelper::translate('Each weight must be a number.'),
            'age.*.required' => TranslationHelper::translate('Each age field is required.'),
            'age.*.integer' => TranslationHelper::translate('Each age must be a number.'),
            'bidding.*.required' => TranslationHelper::translate('Each bidding field is required.'),
            'bidding.*.numeric' => TranslationHelper::translate('Each bidding must be a number.'),
            'image.*.*.required' => TranslationHelper::translate('Each image is required.'),
            'image.*.*.image' => TranslationHelper::translate('Each file must be an image.'),
            'image.*.*.mimes' => TranslationHelper::translate('Only JPEG, JPG, and PNG formats are allowed.'),
            'image.*.*.max' => TranslationHelper::translate('Each image must not exceed 20MB.'),
            'video.*.required' => TranslationHelper::translate('Each video is required.'),
            'video.*.mimes' => TranslationHelper::translate('Only MP4, AVI, WMV, and FLV formats are allowed.'),
            'video.*.max' => TranslationHelper::translate('Each video must not exceed 20MB.'),
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
        ], 422));
    }
}
