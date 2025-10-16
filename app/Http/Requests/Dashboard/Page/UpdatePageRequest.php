<?php

namespace App\Http\Requests\Dashboard\Page;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdatePageRequest extends FormRequest
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
            $rules['description.'.$localeCode] = 'required';
        }
//        $rules['image_svg'] = 'sometimes|mimes:svg';
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
            $messages['name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter  name in '.$properties['name']);
            $messages['description.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter description in '.$properties['name']);
        }

        return $messages;

    }
}
