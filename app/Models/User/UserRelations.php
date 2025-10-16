<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Country;
use App\Models\City;
use App\Models\Nationality;

trait UserRelations {

    /**
     * Get the country that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id')->withTrashed()->select('id', 'name->'.app()->getLocale().' as name', 'image', 'phone_code');
    }

    /**
     * Get the city that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id')->withTrashed()->select('id', 'name->'.app()->getLocale().' as name');
    }

    /**
     * Get the nationality that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id')->withTrashed()->select('id', 'name->'.app()->getLocale().' as name', 'image');
    }


    /**
     * Get the phone country that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phone_country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'phone_country_id')->withTrashed()->select('id', 'name->'.app()->getLocale().' as name', 'image', 'phone_code');
    }
}
