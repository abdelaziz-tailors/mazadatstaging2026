<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscription extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    protected $appends = ['used_auctions'];

    protected $casts = [
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'replaced_at' => 'datetime',
        'package_snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id')->select('id', 'name->'.app()->getLocale().' as name','description->'.app()->getLocale().' as description', 'features', 'coin','price','image', 'subscription_type', 'auctions_limit', 'monthly_price', 'annual_price');
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'approved' 
            && !$this->replaced_at
            && $this->expires_at 
            && $this->expires_at->isFuture() 
            && $this->remaining_auctions > 0;
    }

    /**
     * Check if subscription is pending approval
     */
    public function isPending()
    {
        return $this->status === 'pending' || $this->status === null;
    }

    public function scopePending($query)
    {
        return $query->where(function ($query) {
            $query->where('status', 'pending')->orWhereNull('status');
        });
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
     * Get active subscription for user
     */
    public static function getActiveSubscription($userId)
    {
        $subscription = self::currentSubscription($userId);
        return $subscription && $subscription->isActive() ? $subscription : null;
    }

    public static function currentSubscription($userId, bool $lock = false)
    {
        $query = self::where('user_id', $userId)->where('status', 'approved')
            ->whereNull('replaced_at')->orderByDesc('id');
        return ($lock ? $query->lockForUpdate() : $query)->first();
    }

    public function getUsedAuctionsAttribute(): int
    {
        return max(0, (int) $this->auctions_limit - (int) $this->remaining_auctions);
    }
}
