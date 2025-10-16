<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\UserProfile;

trait UserProfileRelations {
    
    /**
     * Get the user_profile associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user_profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}
