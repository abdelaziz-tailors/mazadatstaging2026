<?php
namespace App\Models\User;

use Illuminate\Support\Facades\Storage;

trait ProviderProfileAccessor {
    public function getProviderProfileCompletedAttribute() {
        if (
            $this->provider_profile &&
            $this->gender != NULL &&
            $this->birth_date != NULL &&
            $this->country != NULL &&
            $this->nationality != NULL &&
            (count(optional($this->provider_profile)->services) > 0) &&
            optional($this->provider_profile)->category != NULL &&
            optional($this->provider_profile)->department != NULL &&
            optional($this->provider_profile)->degree != NULL &&
            optional($this->provider_profile)->experience_years != NULL &&
            (
                optional($this->provider_profile)->foundation == 'oun' ||
                (optional($this->provider_profile)->foundation == 'other' && optional($this->provider_profile)->foundation_name != NULL)
            ) &&
            Storage::disk('public')->exists(optional($this->provider_profile)->scientific_certificate) &&
            Storage::disk('public')->exists(optional($this->provider_profile)->national_id_front) &&
            Storage::disk('public')->exists(optional($this->provider_profile)->national_id_back) &&
            Storage::disk('public')->exists(optional($this->provider_profile)->passport) &&
            Storage::disk('public')->exists(optional($this->provider_profile)->residence) &&
            Storage::disk('public')->exists(optional($this->provider_profile)->signed_contract)
        ) {
            return true;
        }
        else {
            return false;
        }
    }


}
