<?php

namespace App\Http\Requests\Dashboard\Category;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class StoreCategoryRequest extends FormRequest
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

        $rules = array();
        foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $rules['name.'.$localeCode] = 'required';
        }

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
        foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $messages['name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter city name in '.$properties['name']);
        }
        $messages['image_png.required'] = TranslationHelper::translate('Please Choose Png Image');
        $messages['image_png.mimes'] = TranslationHelper::translate('Please Choose Valid Png Image');
        $messages['image_svg.required'] = TranslationHelper::translate('Please Choose SVG Image');
        $messages['image_svg.mimes'] = TranslationHelper::translate('Please Choose Valid SVG Image');
        $messages['coin.required'] = TranslationHelper::translate('Please add coin');


        return $messages;

    }
}
