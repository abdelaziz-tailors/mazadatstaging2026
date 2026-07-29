<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserSubscription;
use App\Models\WalletTransaction;
use App\Support\PartnerDashboardScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerFinanceController extends Controller
{
    public function invoices(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $invoicesQuery = Order::query()
            ->with(['buyer:id,name', 'liveVideo:id,title,title_ar'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('buyer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('filter_status'), function ($query) use ($request) {
                $query->where('status', $request->query('filter_status'));
            })
            ->when($request->filled('filter_payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->query('filter_payment_status'));
            })
            ->when($request->filled('filter_date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->query('filter_date_from'));
            })
            ->when($request->filled('filter_date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->query('filter_date_to'));
            })
            ->latest();

        PartnerDashboardScope::scopeOrders($invoicesQuery);

        $invoices = $invoicesQuery->paginate(15)->appends($request->query());

        $statsBase = Order::query();
        PartnerDashboardScope::scopeOrders($statsBase);

        $thisMonthCount = (clone $statsBase)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $lastMonthCount = (clone $statsBase)->whereYear('created_at', now()->subMonthNoOverflow()->year)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();
        if ($lastMonthCount > 0) {
            $totalTrendPct = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
        } else {
            $totalTrendPct = $thisMonthCount > 0 ? 100.0 : 0.0;
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('payment_status', 'unpaid')->count(),
            'paid' => (clone $statsBase)->where('payment_status', 'paid')->count(),
            'shipping' => (clone $statsBase)->where('status', 'shipping')->count(),
            'total_trend_direction' => $totalTrendPct >= 0 ? 'up' : 'down',
            'total_trend_pct' => abs($totalTrendPct),
        ];

        return view('dashboard.pages.partner-finance.invoices', compact('invoices', 'stats'));
    }

    /**
     * A partner viewing their own wallet sees their real auction earnings
     * (commission/service-fee settlements, from wallet_transactions) —
     * unchanged. The main super-admin viewing this same page instead sees
     * subscription revenue (what partners paid for their packages), since
     * that money never touches wallet_transactions/wallet_balance at all —
     * see UserSubscriptionController::approve(), which only flips the
     * subscriber's user_type/is_verified and never records a wallet entry.
     */
    public function wallet()
    {
        if (PartnerDashboardScope::isPartner()) {
            $admin = Auth::guard('admin')->user();
            $partnerUser = $admin?->user;

            $currentBalance = (float) ($partnerUser->wallet_balance ?? 0);

            $transactions = WalletTransaction::query()
                ->with(['order:id,order_number'])
                ->when($partnerUser?->id, function ($query, $partnerUserId) {
                    $query->where('user_id', $partnerUserId);
                }, function ($query) {
                    $query->whereRaw('1 = 0');
                })
                ->latest()
                ->paginate(15);

            return view('dashboard.pages.partner-finance.wallet', [
                'isPartner' => true,
                'currentBalance' => $currentBalance,
                'transactions' => $transactions,
            ]);
        }

        $approvedSubscriptions = UserSubscription::where('status', 'approved');

        $currentBalance = (float) (clone $approvedSubscriptions)->sum('price');

        $subscriptions = (clone $approvedSubscriptions)
            ->with('user:id,name')
            ->latest()
            ->paginate(15);

        return view('dashboard.pages.partner-finance.wallet', [
            'isPartner' => false,
            'currentBalance' => $currentBalance,
            'subscriptions' => $subscriptions,
        ]);
    }
}
