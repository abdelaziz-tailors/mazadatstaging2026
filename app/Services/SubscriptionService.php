<?php

namespace App\Services;

use App\Helpers\TranslationHelper;
use App\Models\Package;
use App\Models\LiveVideo;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    // All subscription mutations and auction inserts serialize on the same user row.
    private function lockSubscriber(int $userId): User
    {
        // SQLite ignores FOR UPDATE: acquire its writer lock before any reads.
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('users')->where('id', $userId)->update(['id' => DB::raw('id')]);
        }
        return User::whereKey($userId)->lockForUpdate()->firstOrFail();
    }

    public function request(int $userId, Package $package, string $cycle, $image = null): UserSubscription
    {
        return DB::transaction(function () use ($userId, $package, $cycle, $image) {
            $this->lockSubscriber($userId);
            if (UserSubscription::where('user_id', $userId)->pending()->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['package_id' => [TranslationHelper::translate('Your subscription is pending approval')]]);
            }
            $package = Package::whereKey($package->id)->where('is_active', 1)->lockForUpdate()->firstOrFail();
            $path = $image ? $image->store('user/transaction_image', 'public') : null;
            return UserSubscription::create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'subscription_type' => $cycle,
                'duration_months' => $cycle === 'annual' ? 12 : 1,
                'package_snapshot' => $package->getAttributes(),
                'auctions_limit' => $package->auctions_limit,
                'remaining_auctions' => $package->auctions_limit,
                'price' => $cycle === 'annual' ? $package->annual_price : $package->monthly_price,
                'image' => $path,
                'status' => 'pending',
            ]);
        }, 3);
    }

    public function decide(int $id, bool $approve, ?string $reason = null): bool
    {
        $request = UserSubscription::findOrFail($id);
        return DB::transaction(function () use ($request, $approve, $reason) {
            $user = $this->lockSubscriber($request->user_id);
            $request = UserSubscription::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if (!$request->isPending()) {
                return false;
            }
            $changes = ['status' => $approve ? 'approved' : 'rejected', 'rejection_reason' => $approve ? null : $reason];
            if ($approve) {
                if (UserSubscription::where('user_id', $user->id)->pending()->lockForUpdate()->get()->count() > 1) {
                    throw ValidationException::withMessages(['subscription' => [TranslationHelper::translate('Resolve duplicate pending requests before approval')]]);
                }
                $months = (int) ($request->duration_months ?: ($request->subscription_type === 'annual' ? 12 : ($request->subscription_type === 'monthly' ? 1 : 0)));
                if ($months < 1 || (int) $request->auctions_limit < 1) {
                    throw ValidationException::withMessages(['subscription' => [TranslationHelper::translate('Subscription request has no valid saved duration or auction quota')]]);
                }
                $start = now();
                UserSubscription::where('user_id', $user->id)->where('status', 'approved')
                    ->whereNull('replaced_at')->update(['replaced_at' => $start]);
                $changes += [
                    'started_at' => $start,
                    // Legacy requests already saved their cycle and quota; never read the live package.
                    'expires_at' => $start->copy()->addMonthsNoOverflow($months),
                    'remaining_auctions' => $request->auctions_limit,
                ];
                $user->update(['user_type' => 'vendor', 'is_verified' => 1]);
            }
            $request->update($changes);
            $message = $approve ? 'Subscription Approved Successfully' : 'Subscription Rejected Successfully';
            NotificationService::notify($user->id, 'subscription', TranslationHelper::translate($message),
                TranslationHelper::translate($message), ['user_subscription_id' => $request->id, 'status' => $request->status]);
            return true;
        }, 3);
    }

    public function createAuction(array $attributes): LiveVideo
    {
        return $this->saveAuction(new LiveVideo($attributes));
    }

    public function saveAuction(LiveVideo $auction): LiveVideo
    {
        if ($auction->exists) {
            throw new \LogicException('Quota is consumed only when creating an auction.');
        }
        return DB::transaction(function () use ($auction) {
            // A transaction retry must start with a fresh unsaved model.
            $candidate = clone $auction;
            $this->consumeAuction((int) $candidate->user_id);
            if (!$candidate->save()) {
                throw new \RuntimeException('Auction creation was cancelled.');
            }
            return $candidate;
        }, 3);
    }

    private function consumeAuction(int $userId): void
    {
        $this->lockSubscriber($userId);
        $subscription = UserSubscription::currentSubscription($userId, true);
        if (!$subscription || !$subscription->isActive() ||
            !UserSubscription::whereKey($subscription->id)->where('remaining_auctions', '>', 0)
                ->where('expires_at', '>', now())->whereNull('replaced_at')->decrement('remaining_auctions')) {
            throw ValidationException::withMessages(['subscription' => [TranslationHelper::translate('You need to subscribe to create auctions. Please subscribe to continue.')]]);
        }
    }
}
