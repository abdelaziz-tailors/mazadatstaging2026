<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Builder;

trait UserProfileScope {
    public function scopeactiveProvider(Builder $query) {
        return $query->where('type', 'doctor')
            ->where('is_approved',1)
        ->where('profile_completed',1);
        // ->ProviderProfileCompleted()
        // ->whereHas('provider_profile', function ($provider_query) {
        //     $provider_query->where('status', 'approved');
        // });
    }
}
