<?php

namespace App\Http\Requests\Dashboard\Order;

use App\Models\ItemService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreOrderPieceServiceRequest extends FormRequest
{
    private $catalogService;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'order_item_id' => 'required|integer|exists:order_items,id',
            'item_service_id' => 'required|integer|exists:item_services,id',
            'price' => 'nullable|numeric|min:0',
            'price_entry' => 'nullable|string|in:manual,calculated',
        ];

        if ($this->usesCalculation()) {
            $rules['price'] = 'exclude';
            $rules['number_of_animals'] = 'required|integer|min:1|max:2147483647';
            if ($this->catalogMethod() === 'per_animal_per_day') {
                $rules['number_of_days'] = 'required|integer|min:1|max:2147483647';
            }
        }

        return $rules;
    }

    public function servicePrice(): ?string
    {
        if (! $this->usesCalculation()) {
            return $this->input('price');
        }

        $data = $this->validated();
        // The catalog supplies the rate; browser totals, rates and method names are not authoritative.
        $rate = validator(['unit_rate' => $this->catalogService?->getRawOriginal('default_price')], [
            'unit_rate' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'regex:/^\d+(?:\.\d{1,2})?$/D'],
        ], [], $this->attributes())->validate()['unit_rate'];
        $parts = explode('.', (string) $rate);
        $total = (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');

        // Work in halalas and check the existing DECIMAL(12,2) limit before multiplying.
        $factors = $this->catalogMethod() === 'per_animal_per_day'
            ? ['number_of_days', 'number_of_animals']
            : ['number_of_animals'];
        foreach ($factors as $field) {
            $quantity = (int) $data[$field];
            if ($total > intdiv(999999999999, $quantity)) {
                throw ValidationException::withMessages([
                    'unit_rate' => __('service_price.total_too_large'),
                ]);
            }
            $total *= $quantity;
        }

        return intdiv($total, 100).'.'.str_pad((string) ($total % 100), 2, '0', STR_PAD_LEFT);
    }

    private function usesCalculation(): bool
    {
        return $this->input('price_entry') === 'calculated' && $this->catalogMethod() !== 'fixed';
    }

    private function catalogMethod(): string
    {
        $id = filter_var($this->input('item_service_id'), FILTER_VALIDATE_INT);
        if ($id && (! $this->catalogService || (int) $this->catalogService->id !== $id)) {
            $this->catalogService = ItemService::find($id);
        }

        return $id ? ($this->catalogService?->pricingMethod() ?? 'fixed') : 'fixed';
    }

    public function attributes(): array
    {
        return [
            'order_item_id' => __('service_price.order_item'),
            'item_service_id' => __('service_price.service'),
            'price' => __('service_price.price'),
            'price_entry' => __('service_price.entry'),
            'unit_rate' => __('service_price.unit_rate'),
            'number_of_days' => __('service_price.days'),
            'number_of_animals' => __('service_price.animals'),
        ];
    }
}
