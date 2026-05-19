<?php

namespace App\Http\Requests\Dashboard\Slider;

use App\Helpers\TranslationHelper;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSliderRequest extends FormRequest
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
            'image' => 'sometimes|image|mimes:png,jpg,jpeg,webp',
            'link' => 'nullable|url|max:1000',
            'position' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.image' => TranslationHelper::translate('Please choose valid slider image'),
            'image.mimes' => TranslationHelper::translate('Please choose valid slider image'),
            'link.url' => TranslationHelper::translate('Please enter valid slider link'),
            'position.integer' => TranslationHelper::translate('Please enter valid slider position'),
            'position.min' => TranslationHelper::translate('Please enter valid slider position'),
        ];
    }
}
