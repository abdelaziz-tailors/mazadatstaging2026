<?php

namespace App\Http\Requests\Dashboard\Package;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class StorePackageRequest extends FormRequest
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

        // Admin only fills one textarea (one feature bullet per line); mirror
        // it to 'en' the same way name/description already do above, since
        // there's no separate English input for this either.
        $featuresAr = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $this->input('features_text', ''))
        )));

        $this->merge([
            'name' => $name,
            'description' => $description,
            'features' => ['ar' => $featuresAr, 'en' => $featuresAr],
        ]);
    }

    public function rules()
    {

        $rules = array();
        $rules['name.ar'] = 'required';
        $rules['coin'] = 'nullable';
        $rules['price'] = 'nullable';
        $rules['subscription_type'] = 'nullable|in:monthly,annual';
        $rules['auctions_limit'] = 'nullable|integer|min:0';
        $rules['monthly_price'] = 'nullable|numeric|min:0';
        $rules['annual_price'] = 'nullable|numeric|min:0';
        $rules['features.ar'] = 'nullable|array';
        $rules['features.ar.*'] = 'string';

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
