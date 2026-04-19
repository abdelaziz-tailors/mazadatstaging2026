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

        return view('dashboard.pages.auctions.index',compact('request'));
    }




    // get index data by ajax
    public function get_data (Request $request) {
        // dd($re/)




        $providers = LiveVideo::query();
        PartnerDashboardScope::scopeLiveVideos($providers);

        return Datatables::of($providers)

            ->editColumn('title', function(LiveVideo $item) {
                return $item->title;
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
            ->addColumn('auction_time', function(LiveVideo $item) {
                return date('Y-m-d',strtotime($item->date_start_at));

            })

            ->addColumn('action', function(LiveVideo $item) {
                return view('dashboard.pages.videos.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action'])
            ->startsWithSearch()
            -> make(true);
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
