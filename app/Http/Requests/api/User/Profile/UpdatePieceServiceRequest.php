<?php

namespace App\Http\Requests\api\User\Profile;

use App\Helpers\TranslationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdatePieceServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        $rules = [
            'item_service_id' => 'nullable|integer|exists:item_services,id',
            'price' => 'nullable|numeric|min:0',
        ];

        if ($this->has('custom_name')) {
            foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
                $rules['custom_name.'.$localeCode] = 'required|string|max:255';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [];

        foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $messages['custom_name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter  name in '.$properties['name']);
        }

        return $messages;
    }
}
