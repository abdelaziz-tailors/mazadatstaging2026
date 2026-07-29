<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\User\StoreUserRequest;
use App\Http\Requests\Dashboard\User\UpdateUserRequest;
use App\Mail\ApproveEmail;
use App\Mail\SupendedEmail;
use App\Models\City;
use App\Models\Contract;
use App\Models\JobTitle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\ActionTrait;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;

use App\Helpers\TranslationHelper;
use App\Models\Country;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Models\User\User;

class UserController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $this->authorizable('view users');

        if ($request->filled('user_type')) {
            $base = User::where('user_type', $request->user_type);

            $thisMonthCount = (clone $base)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
            $lastMonthCount = (clone $base)->whereYear('created_at', now()->subMonthNoOverflow()->year)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();
            if ($lastMonthCount > 0) {
                $totalTrendPct = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
            } else {
                $totalTrendPct = $thisMonthCount > 0 ? 100.0 : 0.0;
            }

            $stats = [
                'active' => (clone $base)->where('is_active', 1)->count(),
                'inactive' => (clone $base)->where('is_active', 0)->count(),
                'verified' => (clone $base)->where('is_verified', 1)->count(),
                'total' => (clone $base)->count(),
                'total_trend_direction' => $totalTrendPct >= 0 ? 'up' : 'down',
                'total_trend_pct' => abs($totalTrendPct),
            ];
        } else {
            $buyers = User::where('user_type', 'buyer')->count();
            $vendors = User::where('user_type', 'vendor')->count();
            $sellers = User::where('user_type', 'seller')->count();
            $total = User::count();

            $thisMonthCount = User::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
            $lastMonthCount = User::whereYear('created_at', now()->subMonthNoOverflow()->year)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();
            if ($lastMonthCount > 0) {
                $totalTrendPct = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
            } else {
                $totalTrendPct = $thisMonthCount > 0 ? 100.0 : 0.0;
            }

            $stats = [
                'buyers' => $buyers,
                'buyers_pct' => $total > 0 ? round($buyers / $total * 100, 1) : 0,
                'vendors' => $vendors,
                'vendors_pct' => $total > 0 ? round($vendors / $total * 100, 1) : 0,
                'sellers' => $sellers,
                'sellers_pct' => $total > 0 ? round($sellers / $total * 100, 1) : 0,
                'total' => $total,
                'total_trend_direction' => $totalTrendPct >= 0 ? 'up' : 'down',
                'total_trend_pct' => abs($totalTrendPct),
            ];
        }

        return view('dashboard.pages.users.index',compact('request', 'stats'));
    }


    // get index data by ajax
    public function get_data (Request $request) {
        $providers = User::query();
        if ($request->filled('user_type')) {
            $providers->where('user_type', $request->user_type);
        }

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
                    ->with(['item' => $item, 'action' => route('admin.users.active_toogler', $item->id)]);
            })
            ->editColumn('image', function(User $item) {

                return view('dashboard.partials.avatar', ['path' => $item->image, 'name' => $item->name, 'size' => 40])->render();

            })
            ->addColumn('account_type', function(User $item) {
                return $item->account_type ? TranslationHelper::translate($item->account_type) : '-';
            })
            ->editColumn('specialty', function(User $item) {

                return $item->department->name ?? null;

            })
            ->addColumn('action', function(User $item) {
                return view('dashboard.pages.users.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action', 'image'])
            ->startsWithSearch()
            -> make(true);
    }

    /**
     * Show the form for creating a new buyer. Admin-only entry point (the
     * admin is the one accepting responsibility for the account, so unlike
     * the public signup flow there's no terms-and-conditions checkbox here).
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add user');

        $cities = City::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();

        return view('dashboard.pages.users.create', compact('cities'));
    }

    /**
     * Store a newly created buyer in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorizable('add user');

        User::create([
            'name' => $request->name,
            'user_name' => $request->user_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'city_id' => $request->city_id,
            'user_type' => 'buyer',
            'is_active' => 1,
        ]);

        Toastr::success(TranslationHelper::translate('New Buyer Created Successfully'));

        return redirect()->route('admin.users.index', ['user_type' => 'buyer']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit user');
        $user = User::findorfail($id);

        return view('dashboard.pages.users.edit', compact(['user']));
    }

    /**
     * Read-only details page for a single user (linked from the "view"
     * icon in the users table).
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $this->authorizable('view users');
        $user = User::findorfail($id);

        return view('dashboard.pages.users.show', compact(['user']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\UpdateProviderRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $this->authorizable('edit user');
        $provider = User::findorfail($id);

        $provider->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));
        return redirect()->route('admin.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function change_password_form($id)
    {
        $this->authorizable('edit user');
        $user = User::find($id);
        return view('dashboard.pages.users.change_password', compact(['user']));
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function active_toogler ($id, Request $request) {
        $this->authorizable('view users');
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
        $this->authorizable('delete user');
        $admin = User::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
        return redirect()->back();
    }

}
