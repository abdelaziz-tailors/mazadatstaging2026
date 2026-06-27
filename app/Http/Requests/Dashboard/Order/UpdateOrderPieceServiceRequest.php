<?php

namespace App\Http\Requests\Dashboard\Order;

use Illuminate\Foundation\Http\FormRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateOrderPieceServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
}
