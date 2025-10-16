<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Builder;

trait ProviderProfileScope {

    /**
     * Scope a query to only check if profile completed.
     */
    public function scopeProviderProfileCompleted(Builder $query) {
        return $query->where('type', 'provider')
            ->whereNotNull('gender')
            ->whereNotNull('birth_date')
            ->whereHas('country')
            ->whereHas('nationality')
            ->whereHas('provider_profile', function ($profile_query) {
                $profile_query->whereHas('services')
                    ->whereHas('category')
                    ->whereHas('department', function ($department_query) {
                        $department_query->where('slug', ['medical_section', 'services_section']);
                    })
                    ->whereNotNull('degree')
                    ->whereNotNull('experience_years')
                    ->whereNotNull('foundation')
                    ->whereNotNull('scientific_certificate')
                    ->whereNotNull('national_id_front')
                    ->whereNotNull('national_id_back')
                    ->whereNotNull('passport')
                    ->whereNotNull('residence')
                    ->whereNotNull('signed_contract');
            });
    }
}
