<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\ProviderProfile;

trait ProviderProfileRelations {
     /**
     * Get the provider_profile associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function provider_profile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class, 'user_id');
    }
}
