<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Http\Requests\Dashboard\User\UpdateUserRequest;
use App\Mail\ApproveEmail;
use App\Mail\SupendedEmail;
use App\Models\City;
use App\Models\Contract;
use App\Models\Department;
use App\Models\JobTitle;
use App\Models\LiveVideo;
use App\Models\VideoComment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\ActionTrait;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Helpers\TranslationHelper;
use App\Http\Requests\Dashboard\Admin\ChangeHospitalPasswordRequest;
use App\Http\Requests\Dashboard\Providers\StoreProviderRequest;
use App\Http\Requests\Dashboard\Providers\UpdateProviderRequest;
use App\Models\Country;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Models\User\User;
use App\Support\PartnerDashboardScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuctionController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $this->authorizable('view videos');

        $base = LiveVideo::query();
        PartnerDashboardScope::scopeLiveVideos($base);
        $total = (clone $base)->count();

        $thisMonthCount = (clone $base)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $lastMonthCount = (clone $base)->whereYear('created_at', now()->subMonthNoOverflow()->year)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();
        if ($lastMonthCount > 0) {
            $totalTrendPct = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
        } else {
            $totalTrendPct = $thisMonthCount > 0 ? 100.0 : 0.0;
        }

        $stats = [
            'total' => $total,
            'in_progress' => (clone $base)->where('status', 'start')->count(),
            'upcoming' => (clone $base)->where(function (Builder $q) {
                $q->whereNull('status')->orWhereNotIn('status', ['start', 'end']);
            })->count(),
            'archived' => (clone $base)->where('status', 'end')->count(),
            'total_trend_direction' => $totalTrendPct >= 0 ? 'up' : 'down',
            'total_trend_pct' => abs($totalTrendPct),
        ];

        return view('dashboard.pages.auctions.index',compact('request', 'stats'));
    }




    // get index data by ajax
    public function get_data (Request $request) {
        // dd($re/)




        $providers = LiveVideo::query();
        PartnerDashboardScope::scopeLiveVideos($providers);

        if ($request->filled('filter_status')) {
            switch ($request->filter_status) {
                case 'start':
                    $providers->where('status', 'start');
                    break;
                case 'end':
                    $providers->where('status', 'end');
                    break;
                case 'upcoming':
                    $providers->where(function ($q) {
                        $q->whereNull('status')->orWhereNotIn('status', ['start', 'end']);
                    });
                    break;
            }
        }

        if ($request->filled('filter_date_from')) {
            $providers->whereDate('date_start_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $providers->whereDate('date_start_at', '<=', $request->filter_date_to);
        }

        return Datatables::of($providers)

            ->editColumn('title', function(LiveVideo $item) {
                return $item->title_ar;
            })
            ->addColumn('user_name', function(LiveVideo $item) {
                return $item->partner->name ??'';
            })
            ->addColumn('quantity', function(LiveVideo $item) {
                return $item->quantity;
            })
            ->addColumn('start_price', function(LiveVideo $item) {
                return $item->start_price;
            })
            ->addColumn('status', function(LiveVideo $item) {
                return view('dashboard.pages.videos.status')
                    ->with(['item' => $item]);

            })
            ->addColumn('price', function(LiveVideo $item) {
                return  $item->price;
            })
            ->addColumn('buyer', function(LiveVideo $item) {
                return $item->user_auction->name ?? '';
            })
            ->addColumn('start_date', function(LiveVideo $item) {
                return $this->formatAuctionDateTime($item->date_start_at, $item->time_start_at);
            })
            ->addColumn('end_date', function(LiveVideo $item) {
                return $this->formatAuctionDateTime($item->date_end_at, $item->time_end_at);
            })
            ->addColumn('products_count', function(LiveVideo $item) {
                return $item->video_items()->count();
            })
            ->addColumn('participations_count', function(LiveVideo $item) {
                return VideoComment::where('video_id', $item->id)->distinct('user_id')->count('user_id');
            })

            ->addColumn('pieces_action', function(LiveVideo $item) {
                return '<div class="d-flex align-items-center gap-2">'
                    . '<a class="md-icon-btn" href="' . route('admin.products.index', $item->id) . '" title="' . TranslationHelper::translate('view') . '"><i class="fas fa-eye"></i></a>'
                    . '<a class="md-icon-btn" href="' . route('admin.products.create', $item->id) . '" title="' . TranslationHelper::translate('Add Product') . '"><i class="fas fa-plus"></i></a>'
                    . '</div>';
            })

            ->addColumn('action', function(LiveVideo $item) {
                return view('dashboard.pages.auctions.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'start_date', 'end_date', 'pieces_action', 'action'])
            ->startsWithSearch()
            -> make(true);
    }

    /**
     * date_start_at/date_end_at and time_start_at/time_end_at are stored as
     * separate date/time columns, but shown as a single two-line cell: the
     * date, then that same date's time underneath it in the same column —
     * "10:00" (24h, as stored) becomes "10:00 ص" / "10:00 AM" depending on
     * locale. Carbon's own translatedFormat() needs Carbon::setLocale()
     * called explicitly (the app's own locale switch doesn't do that), so
     * AM/PM is translated through the same TranslationHelper convention as
     * everything else in this dashboard instead. Shared by start_date and
     * end_date.
     */
    private function formatAuctionDateTime(?string $date, ?string $time): string
    {
        if (!$date) {
            return '-';
        }

        $dateFormatted = e(date('Y-m-d', strtotime($date)));
        $timeHtml = '';

        if ($time) {
            $parsedTime = Carbon::createFromFormat('H:i', substr($time, 0, 5));
            $meridiem = $parsedTime->format('A') === 'AM' ? 'am' : 'pm';
            $timeHtml = '<small class="text-muted d-block">' . e($parsedTime->format('h:i')) . ' ' . TranslationHelper::translate($meridiem) . '</small>';
        }

        return '<div>' . $dateFormatted . '</div>' . $timeHtml;
    }
    function show($id){
        $video = LiveVideo::findOrFail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($video);
        return view('dashboard.pages.auctions.show',compact('video'));

    }
    public function active_toogler ($id, Request $request) {
        //$this->authorizable('view cities');
        $item = LiveVideo::findorfail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($item);
        $this->trait_active_toogler($item);
    }

}
