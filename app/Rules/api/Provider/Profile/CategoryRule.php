<?php

namespace App\Rules\api\Provider\Profile;

use Illuminate\Contracts\Validation\Rule;

use App\Models\Category;
use App\Helpers\TranslationHelper;

class CategoryRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

     /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Category::where('id', $value)->where('is_active', 1)->whereNull('category_id')
            ->whereHas('department', function ($query) {
                $query->where('is_active', 1)->whereIn('slug', ['medical_section', 'services_section']);
            })
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return TranslationHelper::translate('Choose Valid Main Category');
    }
}
