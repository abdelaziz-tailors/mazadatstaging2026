<?php

namespace App\Http\Requests\Dashboard\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Helpers\TranslationHelper;

class StoreNotificationRequest extends FormRequest
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
        $rules['title'] = 'required';
        $rules['description'] = 'required';

        return $rules;
    }


     /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        $messages['title.required'] = TranslationHelper::translate('Please Enter Notification Title');
        $messages['description.required'] = TranslationHelper::translate('Please Enter Notification Description');

        return $messages;
    }
}
