<?php

namespace App\Http\Requests\Dashboard\City;

use Illuminate\Foundation\Http\FormRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Helpers\TranslationHelper;

class UpdateCityRequest extends FormRequest
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
        return $messages;
    }
}
