<?php

namespace App\Http\Requests\Dashboard\Sound;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\TranslationHelper;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateSoundRequest extends FormRequest
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
        $rules['sound'] = 'sometimes|mimes:audio/mpeg,mpga,mp3,wav,aac';
        $rules['image'] = 'sometimes|mimes:png,jpg,jpeg';

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
            $messages['name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter Sound name in '.$properties['name']);
            $messages['artist_name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter artist name in '.$properties['name']);

        }
        $messages['sound.required'] = TranslationHelper::translate('Please Choose Sound');
        $messages['sound.mimes'] = TranslationHelper::translate('Please Choose Valid Sound');
        $messages['image.required'] = TranslationHelper::translate('Please Choose image');
        $messages['image.mimes'] = TranslationHelper::translate('Please Choose Valid image');


        return $messages;

    }
}
