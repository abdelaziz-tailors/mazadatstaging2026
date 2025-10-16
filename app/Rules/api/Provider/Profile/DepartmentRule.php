<?php

namespace App\Rules\api\Provider\Profile;

use Illuminate\Contracts\Validation\Rule;

use App\Models\Department;
use App\Helpers\TranslationHelper;

class DepartmentRule implements Rule
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
        return Department::where('id', $value)->where('is_active', 1)
            ->whereIn('slug', ['medical_section', 'services_section'])->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return TranslationHelper::translate('Choose Valid Department');
    }
}
