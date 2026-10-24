<?php

namespace App\Http\Requests\api\Video\item;

use App\Helpers\TranslationHelper;
use App\Models\LiveVideo;
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
        $rules = [
            // Ensure each item in the 'title' array is required, a string, and max 255 chars
            'lineage_title_ar' => 'required|array|min:1',
            'lineage_title.*' => 'nullable',
            'lineage_title_ar.*' => 'required',
            'information' => 'nullable',
            'category_id'=>'nullable',
            'category_id.*' => 'required|integer|exists:categories,id', // Assuming category_id references a table
            'partner_id.*' => 'nullable|integer|exists:users,id', // Validate partner_id exists in users table
            'seller_id.*' => 'nullable|integer|exists:users,id',
            'weight' => 'nullable|array',
            'weight.*' => 'nullable|numeric|gt:0',
            'age' => 'nullable',
            'age.*' => 'nullable|string',
            // 'start_price' => 'required', // Assuming price is a number
            // 'start_price.*' => 'numeric', // Assuming price is a number

            'bidding' => 'nullable',
            'bidding.*' => 'numeric',
            'image' => 'required|array|min:1',
            'image.*' => 'required|array|min:1', // Each image in a nested array
            'image.*.*' => 'required|file|image|mimes:jpeg,jpg,png|max:20000', // Each image in a nested array
            'video.*'=>'nullable|mimes:mp4,avi,wmv,flv|max:20000',
            'quantity' => 'sometimes|array',
            'quantity.*' => 'numeric',
            'pieces' => 'nullable|array',
            'pieces.*' => 'required|array',
            'pieces.*.*' => 'required|array',
            'pieces.*.*.age' => 'nullable|string|max:255',
            'pieces.*.*.weight' => 'nullable|numeric|gt:0',
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

        $requiresVideo = LiveVideo::whereKey($this->route('id'))->value('type') === 'recorded';

        foreach ((array) $this->input('lineage_title_ar', []) as $index => $title) {
            $rules["image.$index"] = 'required|array|min:1';
            if ($requiresVideo) {
                $rules["video.$index"] = 'required|file|mimes:mp4,avi,wmv,flv|max:20000';
            }
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            foreach ($this->input('lineage_title_ar', []) as $index => $title) {
                $quantity = (int) $this->input("quantity.$index", 1);
                $pieces = $this->input("pieces.$index", []);
                if ($quantity > 1) {
                    if (count($pieces) !== $quantity) {
                        $validator->errors()->add("pieces.$index", TranslationHelper::translate('pieces_count_must_match_quantity.'));
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
            'weight.*.numeric' => trans('validation.head_weight_numeric'),
            'weight.*.gt' => trans('validation.head_weight_positive'),
            'pieces.*.*.weight.numeric' => trans('validation.head_weight_numeric'),
            'pieces.*.*.weight.gt' => trans('validation.head_weight_positive'),
            'image.required' => trans('validation.animal_images_required'),
            'image.min' => trans('validation.animal_images_required'),
            'image.array' => trans('validation.animal_images_array'),
            'image.*.required' => trans('validation.animal_images_required'),
            'image.*.min' => trans('validation.animal_images_required'),
            'image.*.array' => trans('validation.animal_images_array'),
            'image.*.*.required' => trans('validation.animal_image_file'),
            'image.*.*.file' => trans('validation.animal_image_file'),
            'image.*.*.image' => trans('validation.animal_image_file'),
            'image.*.*.mimes' => trans('validation.animal_image_file'),
            'image.*.*.max' => trans('validation.animal_image_max'),
            'image.*.*.uploaded' => trans('validation.animal_image_uploaded'),
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
            'age.*.required' => TranslationHelper::translate('Each age field is required.'),
            'age.*.integer' => TranslationHelper::translate('Each age must be a number.'),
            'bidding.*.required' => TranslationHelper::translate('Each bidding field is required.'),
            'bidding.*.numeric' => TranslationHelper::translate('Each bidding must be a number.'),
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
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }
}
