<?php

namespace App\Http\Requests\api\SellerSubmission;

use App\Helpers\TranslationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSellerSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('user_type', 'vendor');
                }),
            ],
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'city' => 'nullable|string|max:255',
            'sheep_type' => 'required|string|max:255',
            'age' => 'required|string|max:255',
            'expected_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv|max:204800',
        ];
    }

    public function messages(): array
    {
        return [
            'partner_id.required' => TranslationHelper::translate('please enter partner id'),
            'partner_id.exists' => TranslationHelper::translate('selected partner is invalid'),
            'images.required' => TranslationHelper::translate('please upload at least one image'),
            'images.array' => TranslationHelper::translate('images must be array'),
            'images.min' => TranslationHelper::translate('please upload at least one image'),
        ];
    }
}
