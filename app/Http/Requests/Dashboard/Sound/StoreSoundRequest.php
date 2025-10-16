<?php

namespace App\Http\Requests\Dashboard\Sound;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class StoreSoundRequest extends FormRequest
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
            $rules['artist_name.'.$localeCode] = 'required';
        }
        $rules['sound'] = 'required|mimes:audio/mpeg,mpga,mp3,wav,aac';
        $rules['image'] = 'required|mimes:png,jpg,jpeg';

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
            $messages['name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter sound name in '.$properties['name']);
            $messages['artist_name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter artist name in '.$properties['name']);
        }
        $messages['sound.required'] = TranslationHelper::translate('Please Choose sound');
        $messages['sound.mimes'] = TranslationHelper::translate('Please Choose Valid sound');
        $messages['image.required'] = TranslationHelper::translate('Please Choose image');
        $messages['image.mimes'] = TranslationHelper::translate('Please Choose Valid image');


        return $messages;

    }
}
