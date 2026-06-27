<?php

namespace App\Http\Requests\Dashboard\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderPieceServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_item_id' => 'required|integer|exists:order_items,id',
            'item_service_id' => 'required|integer|exists:item_services,id',
            'price' => 'nullable|numeric|min:0',
        ];
    }
}
