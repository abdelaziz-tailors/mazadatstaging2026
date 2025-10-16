<?php

namespace App\Http\Requests\Dashboard\Country;

use Illuminate\Foundation\Http\FormRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Helpers\TranslationHelper;

class UpdateCountryRequest extends FormRequest
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
        $rules['phone_code'] = 'required|unique:countries,phone_code,'.request()->segment(4).',id,deleted_at,NULL';
        $rules['image'] = 'sometimes|required|mimes:png,jpg,jpeg,webp,gif,svg';
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
            $messages['name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter country name in '.$properties['name']);
        }
        $messages['phone_code.required'] = TranslationHelper::translate('Please Enter Country Phone Code');
        $messages['phone_code.unique'] = TranslationHelper::translate('Country Phone Code is already exists');
        $messages['image.required'] = TranslationHelper::translate('Please Choose Image Of Country Flag');
        $messages['image.mimes'] = TranslationHelper::translate('Please Choose Valid Image Of Country Flag');
        return $messages;
    }
}
