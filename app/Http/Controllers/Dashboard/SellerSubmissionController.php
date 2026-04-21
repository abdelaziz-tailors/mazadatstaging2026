<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\SellerSubmission;
use App\Support\PartnerDashboardScope;
use App\Traits\AuthorizeTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SellerSubmissionController extends Controller
{
    use AuthorizeTrait;

    public function index()
    {
        return view('dashboard.pages.seller-submissions.index', [
            'isPartnerDashboard' => PartnerDashboardScope::isPartner(),
        ]);
    }

    public function get_data(Request $request)
    {
        $items = SellerSubmission::with(['partner', 'user'])->select('seller_submissions.*');
        PartnerDashboardScope::scopeSellerSubmissions($items);

        return Datatables::of($items)
            ->editColumn('name', function (SellerSubmission $item) {
                return $item->user->name ?? '-';
            })
            ->editColumn('phone', function (SellerSubmission $item) {
                return $item->user->phone ?? '-';
            })
            ->addColumn('sheep_type', function (SellerSubmission $item) {
                return $item->sheep_type;
            })
            ->addColumn('partner', function (SellerSubmission $item) {
                return $item->partner->name ?? '-';
            })
            ->addColumn('status_badge', function (SellerSubmission $item) {
                $badge = match ($item->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'needs edit' => 'warning',
                    default => 'secondary',
                };
                return '<span class="badge bg-' . $badge . '">' . $item->status . '</span>';
            })
            ->editColumn('created_at', function (SellerSubmission $item) {
                return Carbon::parse($item->created_at)->format('Y-m-d H:i');
            })
            ->addColumn('action', function (SellerSubmission $item) {
                return view('dashboard.pages.seller-submissions.actions')->with(['item' => $item]);
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $submission = SellerSubmission::with(['media', 'partner'])->findOrFail($id);
        PartnerDashboardScope::ensureOwnSellerSubmission($submission);

        return view('dashboard.pages.seller-submissions.show', [
            'submission' => $submission,
            'isPartnerDashboard' => PartnerDashboardScope::isPartner(),
        ]);
    }

    public function approve($id)
    {
        $submission = SellerSubmission::with('media')->findOrFail($id);
        PartnerDashboardScope::ensureOwnSellerSubmission($submission);

        if ($submission->status === 'approved') {
            Toastr::warning(TranslationHelper::translate('already approved'));
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            // $liveVideo = LiveVideo::create([
            //     'user_id' => $submission->user_id,
            //     'title' => $submission->name . ' - ' . $submission->sheep_type,
            //     'title_ar' => $submission->name . ' - ' . $submission->sheep_type,
            //     'status' => 'pending',
            //     'information' => $submission->description,
            //     'information_ar' => $submission->description,
            //     'city_id' => $submission->city_id,
            //     'partner_id' => $submission->partner_id,
            //     'partners_type' => 'single',
            //     'type' => 'recorded',
            // ]);

            // $images = $submission->media->where('type', 'image')->pluck('path')->values()->toArray();
            // $video = optional($submission->media->firstWhere('type', 'video'))->path;

            // $item = LiveVideoItem::create([
            //     'live_video_id' => $liveVideo->id,
            //     'title' => $submission->sheep_type,
            //     'title_ar' => $submission->sheep_type,
            //     'status' => 'pending',
            //     'user_id' => $submission->partner_id,
            //     'image' => !empty($images) ? json_encode($images) : null,
            //     'information' => $submission->description,
            //     'information_ar' => $submission->description,
            //     'start_price' => $submission->expected_price,
            //     'bidding' => 10,
            //     'age' => $submission->age,
            //     'video' => $video,
            //     'address' => $submission->city->name ?? null,
            // ]);

            $submission->update([
                'status' => 'approved',
                // 'review_note' => null,
                // 'reviewed_by' => Auth::guard('admin')->id(),
                // 'reviewed_at' => now(),
                // 'auction_video_id' => $liveVideo->id,
                // 'auction_item_id' => $item->id,
            ]);

            DB::commit();
            Toastr::success(TranslationHelper::translate('Approved Successfully'));
            return redirect()->back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Toastr::error(TranslationHelper::translate('Something went wrong'));
            return redirect()->back();
        }
    }

    public function reject($id, Request $request)
    {
        $request->validate(['review_note' => 'nullable|string|max:1000']);
        $submission = SellerSubmission::findOrFail($id);
        PartnerDashboardScope::ensureOwnSellerSubmission($submission);

        $submission->update([
            'status' => 'rejected',
            'review_note' => $request->review_note,
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        Toastr::success(TranslationHelper::translate('Rejected Successfully'));
        return redirect()->back();
    }

    public function request_edit($id, Request $request)
    {
        $request->validate(['review_note' => 'required|string|max:1000']);
        $submission = SellerSubmission::findOrFail($id);
        PartnerDashboardScope::ensureOwnSellerSubmission($submission);

        $submission->update([
            'status' => 'needs edit',
            'review_note' => $request->review_note,
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        Toastr::success(TranslationHelper::translate('Updated Successfully'));
        return redirect()->back();
    }
}
