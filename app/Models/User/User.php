<?php

namespace App\Models\User;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\City;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\DoctorTitle;
use App\Models\FollowUser;
use App\Models\Friend;
use App\Models\Gender;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\ProviderDay;
use App\Models\Rate;
use App\Models\Store;
use App\Models\UserCoin;
use App\Models\UserGift;
use App\Models\Video;
use App\Models\VideoFavorites;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use UserRelations;
    use UserAccessor;
    use ProviderProfileAccessor;
    use ProviderProfileScope;
    Use UserProfileScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
    ];
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id')->withTrashed()->select('id','image', 'name->'.app()->getLocale().' as name');
    }
    public function sub_department()
    {
        return $this->belongsTo(Department::class, 'sub_department_id')->withTrashed()->select('id','image', 'name->'.app()->getLocale().' as name');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'add_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->withTrashed()->select('id', 'name->'.app()->getLocale().' as name');
    }
    public function education_city_data()
    {
        return $this->belongsTo(City::class, 'education_city')->withTrashed()->select('id', 'name->'.app()->getLocale().' as name');
    }
    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }


    public function hospital()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }


    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }


    public function doctor_title()
    {
        return $this->belongsTo(DoctorTitle::class, 'job_title')->withTrashed()->select('id', 'name_'.app()->getLocale().' as name');

    }
    public function gender_data()
    {
        return $this->belongsTo(Gender::class, 'gender')->withTrashed()->select('id', 'name_'.app()->getLocale().' as name');

    }

    public function user_coin()
    {
        return $this->hasOne(UserCoin::class, 'user_id');

    }




    public function user_Video(): HasMany
    {
        return $this->hasMany(Video::class, 'user_id');
    }
    public function user_Favorites(): HasMany
    {
        return $this->hasMany(VideoFavorites::class, 'user_id')->whereHas('video');
    }

    public function user_friend_received()
    {
        if(isset(auth('api')->user()->id)){
            return $this->hasOne(Friend::class, 'user_id');
        }else{
            return $this->hasOne(Friend::class, 'user_id','0  ');
        }

    }


    public function user_friend_send()
    {
        if(isset(auth('api')->user()->id)){
            return $this->hasOne(Friend::class, 'friend_id');

        }else{
            return $this->hasOne(Friend::class, 'friend_id','0  ');
        }
   }



    public function user_live_video(): HasOne
    {
        return $this->hasOne(LiveVideo::class, 'user_id')->whereNull('status')->latest();
    }

    public function wonAuctionItemsWithVideos(): HasMany
    {
        return $this->hasMany(LiveVideoItem::class, 'user_finished_id')
            ->whereNotNull('winner_video')
            ->orderBy('id', 'desc');
    }

    public function user_live_video_gifts(): HasMany
    {
        return $this->hasMany(UserGift::class, 'user_video_id');
    }
    public function stories()
    {
        return $this->hasMany(Store::class);
    }



//    public function provider_rate(): HasMany
//    {
//        return $this->hasMany(Rate::class, 'user_id');
//    }

    public function clinic(): HasMany
    {
        return $this->hasMany(Clinic::class, 'user_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rate::class, 'user_id')->where('is_active',1);
    }

    public function follow()
    {
        return $this->hasOne(FollowUser::class,'follow_id','id');
    }
    public function followers()
    {
        return $this->hasMany(FollowUser::class,'follow_id','id');
    }

    public function isfollow()
    {
        // Check if there's a favorite record associated with this post and the current user
        if(isset(auth('api')->user()->id)){
            return $this->follow()->where('user_id', auth('api')->user()->id);

        }else{
            return $this->follow()->where('user_id', '');
        }
    }

//    public function averageRating()
//    {
//        return $this->ratings()->avg('rate');
//    }
//
//

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function isVendor(): bool
    {
        return $this->user_type === 'vendor';
    }
}
