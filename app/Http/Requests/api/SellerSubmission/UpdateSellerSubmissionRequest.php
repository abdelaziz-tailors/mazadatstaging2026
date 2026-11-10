<?php

namespace App\Http\Requests\api\SellerSubmission;

use App\Models\SellerSubmission;

class UpdateSellerSubmissionRequest extends StoreSellerSubmissionRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() && SellerSubmission::whereKey($this->route('id'))
            ->where('user_id', auth('api')->id())->exists();
    }

    public function rules(): array
    {
        $rules = parent::rules();
        // Ownership, destination and review metadata are never client editable.
        $rules['partner_id'] = 'prohibited';
        foreach (['sheep_type', 'age', 'expected_price'] as $field) {
            $rules[$field] = 'sometimes|'.$rules[$field];
        }
        $rules['images'] = 'sometimes|array|min:1';
        return $rules;
    }
}
