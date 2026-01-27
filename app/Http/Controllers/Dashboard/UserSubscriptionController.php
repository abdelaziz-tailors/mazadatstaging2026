<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\User\User;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Helpers\TranslationHelper;
use App\Models\Admin;
use Yajra\DataTables\DataTables;
use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class UserSubscriptionController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // $this->authorizable('view user subscriptions');
        return view('dashboard.pages.user-subscriptions.index');
    }

    /**
     * Get index data by ajax
     */
    public function get_data(Request $request)
    {
        $subscriptions = UserSubscription::with(['user', 'package'])
            ->select('user_subscriptions.*');

        return Datatables::of($subscriptions)
            ->editColumn('user_id', function(UserSubscription $item) {
                return $item->user ? $item->user->name : '-';
            })
            ->editColumn('package_id', function(UserSubscription $item) {
                if ($item->package) {
                    $name = json_decode($item->package->name, true);
                    return $name[app()->getLocale()] ?? '-';
                }
                return '-';
            })
            ->editColumn('subscription_type', function(UserSubscription $item) {
                if ($item->subscription_type) {
                    return $item->subscription_type == 'monthly'
                        ? TranslationHelper::translate('Monthly')
                        : TranslationHelper::translate('Annual');
                }
                return '-';
            })
            ->editColumn('auctions_limit', function(UserSubscription $item) {
                return $item->auctions_limit ?? '-';
            })
            ->editColumn('remaining_auctions', function(UserSubscription $item) {
                return $item->remaining_auctions ?? '-';
            })
            ->editColumn('expires_at', function(UserSubscription $item) {
                if ($item->expires_at) {
                    $expiresAt = Carbon::parse($item->expires_at);
                    $isExpired = $expiresAt->isPast();
                    $badge = $isExpired ? 'danger' : ($expiresAt->diffInDays(now()) <= 7 ? 'warning' : 'success');
                    return '<span class="badge bg-'.$badge.'">' . $expiresAt->format('Y-m-d H:i') . '</span>';
                }
                return '-';
            })
            ->editColumn('status', function(UserSubscription $item) {
                // Handle old records that might not have status field
                $status = $item->status ?? 'pending';
                
                $badge = match($status) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    default => 'secondary'
                };
                $text = match($status) {
                    'approved' => TranslationHelper::translate('Approved'),
                    'pending' => TranslationHelper::translate('Pending'),
                    'rejected' => TranslationHelper::translate('Rejected'),
                    default => TranslationHelper::translate('Pending')
                };
                return '<span class="badge bg-'.$badge.'">' . $text . '</span>';
            })
            ->editColumn('is_active', function(UserSubscription $item) {
                $isActive = $item->isActive();
                $badge = $isActive ? 'success' : 'danger';
                $text = $isActive ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive');
                return '<span class="badge bg-'.$badge.'">' . $text . '</span>';
            })
            ->editColumn('created_at', function(UserSubscription $item) {
                return Carbon::parse($item->created_at)->format('Y-m-d H:i');
            })
            ->addColumn('action', function(UserSubscription $item) {
                return view('dashboard.pages.user-subscriptions.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['expires_at', 'status', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $this->authorizable('view user subscriptions');
        $subscription = UserSubscription::with(['user', 'package'])->findOrFail($id);
        return view('dashboard.pages.user-subscriptions.show', compact('subscription'));
    }

    /**
     * Approve subscription
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        // $this->authorizable('approve user subscription');
        $subscription = UserSubscription::findOrFail($id);
        
        if ($subscription->status === 'approved') {
            Toastr::warning(TranslationHelper::translate('Subscription is already approved'));
            return redirect()->back();
        }
        DB::beginTransaction();
        try {
            $subscription->update([
                'status' => 'approved',
                'rejection_reason' => null,
            ]);

            $subscription->user->update([
                'user_type' => 'vendor',
                'is_verified' => 1,
            ]);

            $admin = Admin::create([
                'name' => $subscription->user->name,
                'email' => $subscription->user->email,
                'phone' => $subscription->user->phone,
                'type' => 'partner',
                'user_id' => $subscription->user_id,
                'password' => bcrypt($subscription->user->password ?? '123456789'),
                'image' => $subscription->user->image ?? 'partners/default.png',
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Toastr::error(TranslationHelper::translate('Failed to approve subscription'));
            return redirect()->back();
        }
        Toastr::success(TranslationHelper::translate('Subscription Approved Successfully'));
        return redirect()->back();
    }

    /**
     * Reject subscription
     *
     * @param  int  $id
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reject($id, Request $request)
    {
        // $this->authorizable('reject user subscription');
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $subscription = UserSubscription::findOrFail($id);
        
        if ($subscription->status === 'rejected') {
            Toastr::warning(TranslationHelper::translate('Subscription is already rejected'));
            return redirect()->back();
        }

        $subscription->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        Toastr::success(TranslationHelper::translate('Subscription Rejected Successfully'));
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $this->authorizable('delete user subscription');
        $subscription = UserSubscription::findOrFail($id);
        $subscription->delete();
        Toastr::success(TranslationHelper::translate('Subscription Deleted Successfully'));
        return redirect()->back();
    }
}
