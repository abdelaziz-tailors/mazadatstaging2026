<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\User\UpdateUserRequest;
use App\Http\Requests\Dashboard\Vendor\StoreVendorRequest;
use App\Http\Requests\Dashboard\Vendor\UpdateVendorRequest;
use App\Mail\ApproveEmail;
use App\Mail\SupendedEmail;
use App\Models\City;
use App\Models\Contract;
use App\Models\Department;
use App\Models\JobTitle;
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
use App\Support\PartnerDashboardScope;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $base = User::where('user_type', 'vendor');
        PartnerDashboardScope::scopeVendors($base);

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', 1)->count();

        $thisMonthCount = (clone $base)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $lastMonthCount = (clone $base)->whereYear('created_at', now()->subMonthNoOverflow()->year)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();
        if ($lastMonthCount > 0) {
            $totalTrendPct = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
        } else {
            $totalTrendPct = $thisMonthCount > 0 ? 100.0 : 0.0;
        }

        $stats = [
            'total' => $total,
            'total_trend_direction' => $totalTrendPct >= 0 ? 'up' : 'down',
            'total_trend_pct' => abs($totalTrendPct),
            'active' => $active,
            'active_pct' => $total > 0 ? round($active / $total * 100, 1) : 0,
            'inactive' => $total - $active,
            'inactive_pct' => $total > 0 ? round(($total - $active) / $total * 100, 1) : 0,
            'new_this_month' => $thisMonthCount,
            'new_this_month_trend_direction' => $totalTrendPct >= 0 ? 'up' : 'down',
            'new_this_month_trend_pct' => abs($totalTrendPct),
        ];

        return view('dashboard.pages.vendors.index', compact('request', 'stats'));
    }

    /**
     * Read-only details page for a single vendor (linked from the "view"
     * icon in the vendors table).
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $vendor = User::where('user_type', 'vendor')->findOrFail($id);

        return view('dashboard.pages.vendors.show', compact('vendor'));
    }


    // get index data by ajax
    public function get_data (Request $request) {
        $providers = User::where('user_type', 'vendor');
        PartnerDashboardScope::scopeVendors($providers);

        if ($request->filled('filter_username')) {
            $providers->where('name', 'like', '%'.$request->filter_username.'%');
        }

        if ($request->filled('filter_email')) {
            $providers->where('email', 'like', '%'.$request->filter_email.'%');
        }

        if ($request->filled('filter_status') && in_array($request->filter_status, ['1', '0'], true)) {
            $providers->where('is_active', $request->filter_status);
        }

        if ($request->filled('filter_date_from')) {
            $providers->whereDate('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $providers->whereDate('created_at', '<=', $request->filter_date_to);
        }


        return Datatables::of($providers)

            ->editColumn('name', function(User $item) {
                return $item->name;
            })
            ->addColumn('user_name', function(User $item) {
                return $item->user_name;
            })
            ->addColumn('age', function(User $item) {


                return Carbon::parse( $item['birth_date'])->age;

            })
            ->editColumn('created_at', function(User $item) {
                return date('Y-m-d',strtotime($item->created_at));
            })

            ->editColumn('is_active', function(User $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.vendors.active_toogler', $item->id)]);
            })
            ->editColumn('image', function(User $item) {

                return view('dashboard.pages.vendors.image')
                    ->with(['item' => $item]);

            })
            ->editColumn('specialty', function(User $item) {

                return $item->department->name ?? null;

            })
            ->addColumn('action', function(User $item) {
                return view('dashboard.pages.vendors.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action'])
            ->startsWithSearch()
            -> make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $cities = City::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();

        return view('dashboard.pages.vendors.create', compact('cities'));
    }

    /**
     * Store a newly created vendor in storage. Admin-only entry point (the
     * admin is the one accepting responsibility for the account, so unlike
     * a public signup flow there's no terms-and-conditions checkbox here) —
     * matches the buyer creation flow (App\Http\Controllers\Dashboard\UserController::store()).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreVendorRequest $request)
    {
        User::create([
            'name' => $request->name,
            'user_name' => $request->user_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'city_id' => $request->city_id,
            'admin_id' => Auth::guard('admin')->user()->id,
            'user_type' => 'vendor',
            'is_active' => 1,
        ]);

        Toastr::success(TranslationHelper::translate('New Vendor Created Successfully'));

        return redirect()->route('admin.vendors.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $user = User::findorfail($id);
        PartnerDashboardScope::ensureOwnVendor($user);

        return view('dashboard.pages.vendors.edit', compact(['user']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\UpdateProviderRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVendorRequest $request, $id)
    {
        $provider = User::findorfail($id);
        PartnerDashboardScope::ensureOwnVendor($provider);

        $provider->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));
        return redirect()->route('admin.vendors.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function change_password_form($id)
    {
        $user = User::find($id);
        return view('dashboard.pages.vendors.change_password', compact(['user']));
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function active_toogler ($id, Request $request) {
        $item = User::findorfail($id);
        $this->trait_active_toogler($item);
    }

    public function save_password(ChangeHospitalPasswordRequest $request, $id)
    {
        $admin = User::findorfail($id);
        $admin->update([
            'password' => bcrypt($request->password)
        ]);
        Toastr::success(TranslationHelper::translate('Administrator Password Changed Successfully'));
        return redirect()->route('admin.providers.index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = User::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
        return redirect()->back();
    }

}
