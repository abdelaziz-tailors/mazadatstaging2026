<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\SellerSubmission;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use App\Helpers\TranslationHelper;
use App\Support\PartnerDashboardScope;

use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Chart date-range options (days) offered by the "آخر N يوم" selector
     * shared by the three chart cards on the super-admin home page.
     */
    private const ALLOWED_CHART_DAYS = [7, 30, 90];

    public function index(?Request $request = null) {

        $request = $request ?? request();
        $days = (int) $request->query('days', 30);
        if (!in_array($days, self::ALLOWED_CHART_DAYS, true)) {
            $days = 30;
        }

        if (Auth::guard('admin')->user()->type=="admin") {

            $sales = LiveVideoItem::whereNotNull('finished_price');
            $salesTotal = (clone $sales)->sum('finished_price');
            $salesTrend = $this->monthOverMonthTrend((clone $sales), 'finished_price');

            $usersTotal = User::count();
            $usersTrend = $this->monthOverMonthTrend(User::query());

            $partnersTotal = Admin::where('type', 'partner')->count();
            $partnersTrend = $this->monthOverMonthTrend(Admin::where('type', 'partner'));

            $auctionItemsTotal = LiveVideoItem::count();
            $auctionItemsTrend = $this->monthOverMonthTrend(LiveVideoItem::query());

            $activeAuctionsTotal = LiveVideo::where('status', 'start')->count();
            // A "currently active" count is a snapshot, not a running total, so
            // there is no true historical value to diff against — this trend
            // instead reflects growth in newly-created active auctions
            // (status='start' rows, bucketed by their own created_at), the
            // same real month-over-month convention used for every other
            // card here. Documented in docs/dashboard-redesign-ar.md.
            $activeAuctionsTrend = $this->monthOverMonthTrend(LiveVideo::where('status', 'start'));

            $vendorsTotal = User::where('user_type', 'vendor')->count();
            $vendorsTrend = $this->monthOverMonthTrend(User::where('user_type', 'vendor'));

            $ordersTotal = Order::count();
            $ordersTrend = $this->monthOverMonthTrend(Order::query());

            $reports = [
                ['name' => TranslationHelper::translate('Sales'), 'value' => $salesTotal, 'color' => 'success', 'icon' => 'fa-solid fa-coins', 'trend' => $salesTrend, 'is_currency' => true],
                ['name' => TranslationHelper::translate('Users'), 'value' => $usersTotal, 'color' => 'success', 'icon' => 'fa-solid fa-user', 'trend' => $usersTrend],
                ['name' => TranslationHelper::translate('Partners'), 'value' => $partnersTotal, 'color' => 'success', 'icon' => 'fa-solid fa-user-tie', 'trend' => $partnersTrend],
                ['name' => TranslationHelper::translate('Auctions Product'), 'value' => $auctionItemsTotal, 'color' => 'success', 'icon' => 'fa-solid fa-boxes-stacked', 'trend' => $auctionItemsTrend],
                ['name' => TranslationHelper::translate('active_auctions_dashboard'), 'value' => $activeAuctionsTotal, 'color' => 'success', 'icon' => 'fa-solid fa-video', 'trend' => $activeAuctionsTrend],
                ['name' => TranslationHelper::translate('Vendors'), 'value' => $vendorsTotal, 'color' => 'success', 'icon' => 'fa-solid fa-truck-fast', 'trend' => $vendorsTrend],
                ['name' => TranslationHelper::translate('Orders'), 'value' => $ordersTotal, 'color' => 'success', 'icon' => 'fa-solid fa-cart-shopping', 'trend' => $ordersTrend],
            ];

            $registrationsChart = $this->dailySeries(User::query(), null, $days);
            $salesChart = $this->dailySeries(LiveVideoItem::query()->whereNotNull('finished_price'), 'finished_price', $days);
            $statusChart = $this->statusBreakdown(LiveVideo::query()->where('created_at', '>=', now()->subDays($days - 1)->startOfDay()));

            $registrationsTrend = $this->periodOverPeriodTrend(User::query(), $days);
            $salesTrend = $this->periodOverPeriodTrend(LiveVideoItem::query()->whereNotNull('finished_price'), $days, 'finished_price');

            $latestUsers = User::orderBy('id', 'desc')->limit(3)->get();
            $latestAuctions = LiveVideo::with('partner')->orderBy('id', 'desc')->limit(3)->get();

            $pendingSellerSubmissions = SellerSubmission::whereNotIn('status', ['approved', 'rejected'])->count();
            $pendingSubscriptions = UserSubscription::where('status', 'pending')->orWhereNull('status')->count();
            $pendingReviewCount = $pendingSellerSubmissions + $pendingSubscriptions;

        }else{

            $vendor = User::where('admin_id',Auth::guard('admin')->user()->id)->where('user_type','vendor')->count();
            $LiveVideo = LiveVideo::where('admin_id',Auth::guard('admin')->user()->id)->count();
            $LiveVideoItem = LiveVideoItem::whereHas('videoLive', function($query) {
                $query->where('admin_id',Auth::guard('admin')->user()->id);
            })->count();

            $salesQuery = LiveVideoItem::whereHas('videoLive', function($query) {
                $query->where('admin_id',Auth::guard('admin')->user()->id);
            });
            $sales = (clone $salesQuery)->sum('finished_price');

            $reports = array();
            $reports[] = array('name'=>TranslationHelper::translate('Vendors'), 'value'=>$vendor, 'color'=>'success',  'icon'=>'fa-solid fa-user-tie');
            $reports[] = array('name'=>TranslationHelper::translate('Auctions'), 'value'=>$LiveVideo, 'color'=>'success',  'icon'=>'fa-solid fa-video');
            $reports[] = array('name'=>TranslationHelper::translate('Auctions Product'), 'value'=>$LiveVideoItem, 'color'=>'success',  'icon'=>'fa-solid fa-list');
            $reports[] = array('name'=>TranslationHelper::translate('Sales'), 'value'=>$sales, 'color'=>'success',  'icon'=>'fa-solid fa-coins');

            $registrationsChart = null;
            $salesChart = $this->dailySeries(
                LiveVideoItem::query()->whereNotNull('finished_price')->whereHas('videoLive', function ($query) {
                    $query->where('admin_id', Auth::guard('admin')->user()->id);
                }),
                'finished_price',
                $days
            );
            $statusQuery = LiveVideo::query()->where('created_at', '>=', now()->subDays($days - 1)->startOfDay());
            PartnerDashboardScope::scopeLiveVideos($statusQuery);
            $statusChart = $this->statusBreakdown($statusQuery);

            $registrationsTrend = null;
            $salesTrend = $this->periodOverPeriodTrend(
                LiveVideoItem::query()->whereNotNull('finished_price')->whereHas('videoLive', function ($query) {
                    $query->where('admin_id', Auth::guard('admin')->user()->id);
                }),
                $days,
                'finished_price'
            );

            $latestUsers = collect();
            $latestAuctions = LiveVideo::with('partner')->where('admin_id', Auth::guard('admin')->user()->id)
                ->orderBy('id', 'desc')->limit(3)->get();

            $pendingReviewCount = 0;
        }


        return view('dashboard.home', compact(
            'reports', 'registrationsChart', 'salesChart', 'statusChart', 'latestUsers', 'latestAuctions',
            'registrationsTrend', 'salesTrend', 'days', 'pendingReviewCount'
        ));

    }

    /**
     * Real month-over-month comparison, shared by every stat card's trend
     * badge: this-calendar-month's new rows (or SUM($sumColumn) over them)
     * vs last-calendar-month's, both bucketed by the query's own
     * created_at — the same convention already used across every other
     * dashboard list page's "total" trend in this app.
     */
    private function monthOverMonthTrend($query, ?string $sumColumn = null, string $dateColumn = 'created_at'): array
    {
        $thisMonth = (clone $query)
            ->whereYear($dateColumn, now()->year)
            ->whereMonth($dateColumn, now()->month);
        $lastMonth = (clone $query)
            ->whereYear($dateColumn, now()->subMonthNoOverflow()->year)
            ->whereMonth($dateColumn, now()->subMonthNoOverflow()->month);

        $thisValue = $sumColumn ? (float) $thisMonth->sum($sumColumn) : $thisMonth->count();
        $lastValue = $sumColumn ? (float) $lastMonth->sum($sumColumn) : $lastMonth->count();

        if ($lastValue > 0) {
            $pct = round((($thisValue - $lastValue) / $lastValue) * 100, 1);
        } else {
            $pct = $thisValue > 0 ? 100.0 : 0.0;
        }

        return ['direction' => $pct >= 0 ? 'up' : 'down', 'pct' => abs($pct)];
    }

    /**
     * Real period-over-period comparison for the "last N days" chart cards'
     * headline number (e.g. "46 new users" / "1,248,750 SAR"): current
     * N-day window vs the N-day window immediately before it. Kept separate
     * from monthOverMonthTrend() (used by the top stat-grid cards), which is
     * always calendar-month bucketed regardless of the selected day range.
     */
    private function periodOverPeriodTrend($query, int $days, ?string $sumColumn = null, string $dateColumn = 'created_at'): array
    {
        $currentStart = Carbon::now()->subDays($days - 1)->startOfDay();
        $currentEnd = Carbon::now()->endOfDay();
        $previousStart = $currentStart->copy()->subDays($days);
        $previousEnd = $currentStart->copy()->subSecond();

        $currentValue = $sumColumn
            ? (float) (clone $query)->whereBetween($dateColumn, [$currentStart, $currentEnd])->sum($sumColumn)
            : (float) (clone $query)->whereBetween($dateColumn, [$currentStart, $currentEnd])->count();
        $previousValue = $sumColumn
            ? (float) (clone $query)->whereBetween($dateColumn, [$previousStart, $previousEnd])->sum($sumColumn)
            : (float) (clone $query)->whereBetween($dateColumn, [$previousStart, $previousEnd])->count();

        if ($previousValue > 0) {
            $pct = round((($currentValue - $previousValue) / $previousValue) * 100, 1);
        } else {
            $pct = $currentValue > 0 ? 100.0 : 0.0;
        }

        return ['direction' => $pct >= 0 ? 'up' : 'down', 'pct' => abs($pct), 'value' => $currentValue];
    }

    /**
     * Build a daily series (count, or sum of $column) for a chart over the
     * last $days days, with zero-filled gaps for days with no rows.
     */
    private function dailySeries($query, ?string $column = null, int $days = 30): array
    {
        $since = Carbon::now()->subDays($days - 1)->startOfDay();

        $select = $column ? "DATE(created_at) as d, SUM($column) as v" : 'DATE(created_at) as d, COUNT(*) as v';

        $rows = $query->where('created_at', '>=', $since)
            ->selectRaw($select)
            ->groupBy('d')
            ->pluck('v', 'd');

        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $since->copy()->addDays($i);
            $labels[] = $date->translatedFormat('j M');
            $values[] = (float) ($rows[$date->format('Y-m-d')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Bucket LiveVideo rows into active/scheduled/ended based on the raw `status` column
     * (mirrors the same mapping used by dashboard.pages.videos.status).
     */
    private function statusBreakdown($query): array
    {
        $rows = $query->selectRaw("
                CASE
                    WHEN status = 'start' THEN 'active'
                    WHEN status = 'end' THEN 'ended'
                    ELSE 'scheduled'
                END as bucket,
                COUNT(*) as c
            ")
            ->groupBy('bucket')
            ->pluck('c', 'bucket');

        return [
            'active' => (int) ($rows['active'] ?? 0),
            'scheduled' => (int) ($rows['scheduled'] ?? 0),
            'ended' => (int) ($rows['ended'] ?? 0),
        ];
    }

    public function appointment(){

        return view('dashboard.appointment');


    }
}
