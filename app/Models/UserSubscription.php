<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id')->select('id', 'name->'.app()->getLocale().' as name','description->'.app()->getLocale().' as description', 'coin','price','image', 'subscription_type', 'auctions_limit', 'monthly_price', 'annual_price');
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'approved' 
            && $this->expires_at 
            && $this->expires_at->isFuture() 
            && $this->remaining_auctions > 0;
    }

    /**
     * Check if subscription is pending approval
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if subscription is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if subscription is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Decrement remaining auctions
     */
    public function decrementAuctions()
    {
        if ($this->remaining_auctions > 0) {
            $this->decrement('remaining_auctions');
        }
    }

    /**
     * Get active subscription for user
     */
    public static function getActiveSubscription($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->where('remaining_auctions', '>', 0)
            ->latest()
            ->first();
    }
}
