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
        $rules['description.ar'] = 'required';
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
        $messages['name.ar.required'] = TranslationHelper::translate('Please enter  name in Arabic');
        $messages['description.ar.required'] = TranslationHelper::translate('Please enter description in Arabic');

        return $messages;

    }
}
