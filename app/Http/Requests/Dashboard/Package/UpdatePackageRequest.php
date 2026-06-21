<?php

namespace App\Http\Requests\Dashboard\Package;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdatePackageRequest extends FormRequest
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
    protected function prepareForValidation()
    {
        $name = $this->input('name', []);
        $description = $this->input('description', []);

        $name['en'] = $name['en'] ?? ($name['ar'] ?? '');
        $description['en'] = $description['en'] ?? ($description['ar'] ?? '');

        $this->merge([
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function rules()
    {

        $rules = array();
        $rules['name.ar'] = 'required';
//        $rules['image_svg'] = 'sometimes|mimes:svg';
        $rules['coin'] = 'nullable';
        $rules['price'] = 'nullable';
        $rules['subscription_type'] = 'nullable|in:monthly,annual';
        $rules['auctions_limit'] = 'nullable|integer|min:0';
        $rules['monthly_price'] = 'nullable|numeric|min:0';
        $rules['annual_price'] = 'nullable|numeric|min:0';
        return $rules;
    }


     /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {


        $messages = array();
        $messages['name.ar.required'] = TranslationHelper::translate('Please enter city name in Arabic');
        $messages['image_png.required'] = TranslationHelper::translate('Please Choose Png Image');
        $messages['image_png.mimes'] = TranslationHelper::translate('Please Choose Valid Png Image');
        $messages['image_svg.required'] = TranslationHelper::translate('Please Choose SVG Image');
        $messages['image_svg.mimes'] = TranslationHelper::translate('Please Choose Valid SVG Image');
        $messages['coin.required'] = TranslationHelper::translate('Please add coin');

        return $messages;

    }
}
