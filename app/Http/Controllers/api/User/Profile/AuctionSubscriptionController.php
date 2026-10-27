<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class AuctionSubscriptionController extends Controller
{
    use ResponseTrait;

    /**
     * Get available auction subscription plans
     */
    public function getPlans(): JsonResponse
    {

        $plans = Package::where('is_active', 1)
            // ->whereNotNull('subscription_type')
            ->select('id', 'name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description', 'features',
                      'auctions_limit', 'monthly_price', 'annual_price', 'image')
            ->get()
            ->map(function (Package $plan) {
                $plan->features = $plan->featuresList();
                $plan->is_current = auth('api')->check() && optional(UserSubscription::currentSubscription(auth('api')->id()))->package_id == $plan->id;

                return $plan;
            });

        return $this->success_response(TranslationHelper::translate('Successfully'), $plans);
    }

    /**
     * Subscribe to an auction plan
     */
    public function subscribe(Request $request): JsonResponse
    {
        if (!auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'subscription_type' => 'required|in:monthly,annual',
            'transaction_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $package = Package::findOrFail($request->package_id);

        $subscription = $this->createPendingSubscription(
            auth('api')->user()->id,
            $package,
            $request->subscription_type,
            $request->file('transaction_image')
        );

        return $this->success_response(
            TranslationHelper::translate('Subscription created successfully. Waiting for admin approval.'),
            $this->serializeSubscription($subscription)
        );
    }

    /**
     * Renew the user's current (or most recent) auction subscription.
     * Defaults to the same package and billing cycle they're already on;
     * package_id/subscription_type may optionally be passed to switch plans
     * while renewing. Creates a new pending subscription — same
     * admin-approval workflow as a first-time subscribe.
     */
    public function renew(Request $request): JsonResponse
    {
        if (!auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $latestSubscription = UserSubscription::where('user_id', auth('api')->user()->id)
            ->orderByDesc('id')
            ->first();

        if (!$latestSubscription) {
            return $this->failed_response(TranslationHelper::translate('No subscription to renew'));
        }

        $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'subscription_type' => 'nullable|in:monthly,annual',
            'transaction_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $package = Package::findOrFail($request->package_id ?? $latestSubscription->package_id);
        $subscriptionType = $request->subscription_type ?? $latestSubscription->subscription_type;

        $subscription = $this->createPendingSubscription(
            auth('api')->user()->id,
            $package,
            $subscriptionType,
            $request->file('transaction_image')
        );

        return $this->success_response(
            TranslationHelper::translate('Subscription renewal requested successfully. Waiting for admin approval.'),
            $this->serializeSubscription($subscription)
        );
    }

    /**
     * Shared by subscribe() and renew(): a new subscription always starts
     * 'pending' and awaits admin approval, regardless of whether it's a
     * first-time signup or a renewal of an existing plan.
     */
    private function createPendingSubscription(int $userId, Package $package, string $subscriptionType, $transactionImage): UserSubscription
    {
        return app(\App\Services\SubscriptionService::class)->request($userId, $package, $subscriptionType, $transactionImage);
    }

    /**
     * Get current user's subscription status
     */
    public function getStatus(): JsonResponse
    {
        if (!auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $activeSubscription = UserSubscription::getActiveSubscription(auth('api')->user()->id);
        $latestSubscription = UserSubscription::where('user_id', auth('api')->user()->id)
            ->orderByDesc('id')
            ->first();

        if (!$latestSubscription) {
            return $this->success_response(
                TranslationHelper::translate('No subscription'),
                [
                    'has_subscription' => false,
                    'message' => TranslationHelper::translate('You need to subscribe to create auctions')
                ]
            );
        }

        $latestSubscription = UserSubscription::currentSubscription(auth('api')->id()) ?? $latestSubscription;
        $package = $latestSubscription->package;

        $response = [
            'has_subscription' => true,
            'pending_request' => ($pending = UserSubscription::where('user_id', auth('api')->id())->pending()->first())
                ? $this->serializeSubscription($pending) : null,
            'subscription' => [
                'id' => $latestSubscription->id,
                'status' => $latestSubscription->status,
                'subscription_type' => $latestSubscription->subscription_type,
                'price' => $latestSubscription->price,
                'auctions_limit' => $latestSubscription->auctions_limit,
                'remaining_auctions' => $latestSubscription->remaining_auctions,
                'used_auctions' => $latestSubscription->used_auctions,
                'package_snapshot' => $this->serializeSubscription($latestSubscription)['package_snapshot'],
                'started_at' => optional($latestSubscription->started_at ?? ($latestSubscription->status === 'approved' ? $latestSubscription->created_at : null))->format('Y-m-d'),
                'is_active' => $latestSubscription->isActive(),
                'replaced_at' => $latestSubscription->replaced_at,
                'expires_at' => $latestSubscription->expires_at ? $latestSubscription->expires_at->format('Y-m-d H:i:s') : null,
                'package' => $package ? $this->localizePackage($package->toArray()) : null,
                'rejection_reason' => $latestSubscription->rejection_reason,
            ]
        ];

        if ($latestSubscription->status === 'pending') {
            $response['message'] = TranslationHelper::translate('Your subscription is pending approval');
        } elseif ($latestSubscription->status === 'rejected') {
            $response['message'] = TranslationHelper::translate('Your subscription was rejected');
        } elseif ($activeSubscription) {
            $response['message'] = TranslationHelper::translate('Your subscription is active');
        } else {
            $response['message'] = TranslationHelper::translate('Your subscription is not active');
        }

        return $this->success_response(
            TranslationHelper::translate('Successfully'),
            $response
        );
    }

    /**
     * Get all user subscriptions history
     */
    public function getHistory(): JsonResponse
    {
        if (!auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $subscriptions = UserSubscription::where('user_id', auth('api')->user()->id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->orderByDesc('id')
            ->get();

        return $this->success_response(TranslationHelper::translate('Successfully'), $subscriptions->map(function (UserSubscription $subscription) {
            return $this->serializeSubscription($subscription);
        }));
    }

    /** Localize response copies only; never assign display values to saved models. */
    private function serializeSubscription(UserSubscription $subscription): array
    {
        $data = $subscription->toArray();
        if (is_array($data['package_snapshot'] ?? null)) {
            $data['package_snapshot'] = $this->localizePackage($data['package_snapshot']);
        }
        // Legacy records retain a null snapshot and display the related package.
        if ($subscription->package_snapshot === null || array_key_exists('package', $data)) {
            $data['package'] = $subscription->package
                ? $this->localizePackage($subscription->package->toArray()) : null;
        }

        return $data;
    }

    private function localizePackage(array $data): array
    {
        foreach (['name', 'description'] as $field) {
            $value = TranslationHelper::localizedValue($data[$field] ?? null);
            $data[$field] = is_string($value) ? $value : '';
        }
        $data['features'] = (new Package($data))->featuresList();

        return $data;
    }
}
