<?php

namespace App\Http\Requests\api\User\Profile;

use App\Helpers\TranslationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class StorePieceServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        $rules = [
            'order_item_id' => 'required|integer|exists:order_items,id',
            'item_service_id' => 'nullable|integer|exists:item_services,id',
            'price' => 'nullable|numeric|min:0',
        ];

        if (! $this->input('item_service_id')) {
            $rules['price'] = 'required|numeric|min:0';

            foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
                $rules['custom_name.'.$localeCode] = 'required|string|max:255';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'order_item_id.required' => TranslationHelper::translate('order_item_id_required'),
            'price.required' => TranslationHelper::translate('price_required'),
        ];

        foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $messages['custom_name.'.$localeCode.'.required'] = TranslationHelper::translate('Please enter  name in '.$properties['name']);
        }

        return $messages;
    }
}
