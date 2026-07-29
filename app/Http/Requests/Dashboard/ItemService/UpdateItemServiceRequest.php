<?php

namespace App\Http\Requests\Dashboard\ItemService;

use App\Helpers\TranslationHelper;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Item services only collect an Arabic name (the English name field was
 * removed from the form) — unlike the generic StoreNameRequest/
 * UpdateNameRequest shared by categories/ages/animal-pens/etc., which still
 * require every supported locale and must not be touched here.
 */
class UpdateItemServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name.ar' => 'required|string|max:255',
            'default_price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.ar.required' => TranslationHelper::translate('Please enter item service name'),
        ];
    }
}
