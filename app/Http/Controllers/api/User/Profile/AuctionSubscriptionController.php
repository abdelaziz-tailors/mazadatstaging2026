<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->select('id', 'name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description',
                      'auctions_limit', 'monthly_price', 'annual_price', 'image')
            ->get();

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

        // if (!$package->subscription_type) {
        //     return $this->failed_response(TranslationHelper::translate('This package is not available for auction subscriptions'));
        // }

        // Calculate expiration date
        $expiresAt = $request->subscription_type === 'monthly'
            ? Carbon::now()->addMonth()
            : Carbon::now()->addYear();

        // Get price based on subscription type
        $price = $request->subscription_type === 'monthly'
            ? $package->monthly_price
            : $package->annual_price;

        // Handle transaction image if provided
        $imageName = null;
        if ($request->hasFile('transaction_image')) {
            $image = $request->file('transaction_image');
            $imageName = 'user/transaction_image/'.rand(11111, 99999).'_'.$image->getClientOriginalName();
            $image->move(public_path('../storage/app/public/user/transaction_image'), $imageName);
        }

        // Create subscription with pending status
        $subscription = UserSubscription::create([
            'user_id' => auth('api')->user()->id,
            'package_id' => $package->id,
            'subscription_type' => $request->subscription_type,
            'auctions_limit' => $package->auctions_limit,
            'remaining_auctions' => $package->auctions_limit,
            'expires_at' => $expiresAt,
            'price' => $price,
            'image' => $imageName,
            'status' => 'pending', // Set status to pending for admin approval
        ]);

        return $this->success_response(
            TranslationHelper::translate('Subscription created successfully. Waiting for admin approval.'),
            $subscription
        );
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
            ->latest()
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

        $response = [
            'has_subscription' => true,
            'subscription' => [
                'id' => $latestSubscription->id,
                'status' => $latestSubscription->status,
                'subscription_type' => $latestSubscription->subscription_type,
                'auctions_limit' => $latestSubscription->auctions_limit,
                'remaining_auctions' => $latestSubscription->remaining_auctions,
                'expires_at' => $latestSubscription->expires_at ? $latestSubscription->expires_at->format('Y-m-d H:i:s') : null,
                'package' => $latestSubscription->package,
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
            ->get();

        return $this->success_response(TranslationHelper::translate('Successfully'), $subscriptions);
    }
}
