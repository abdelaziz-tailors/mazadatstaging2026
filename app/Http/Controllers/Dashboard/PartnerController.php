<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Partner\StorePartnerRequest;
use App\Http\Requests\Dashboard\Partner\UpdatePartnerRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use App\Helpers\TranslationHelper;
use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;

use App\Models\Admin;
use Spatie\Permission\Models\Role;

use App\Http\Requests\Dashboard\Admin\StoreAdminRequest;
use App\Http\Requests\Dashboard\Admin\UpdateAdminRequest;
use App\Http\Requests\Dashboard\Admin\ChangeAdminPasswordRequest;
use App\Models\User\User;

class PartnerController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->authorizable('view partners');

        $base = Admin::where('type', 'partner');
        $total = (clone $base)->count();
        $active = (clone $base)->whereHas('user', function ($query) {
            $query->where('is_active', 1);
        })->count();
        $inactive = $total - $active;

        $thisMonthCount = (clone $base)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $lastMonthCount = (clone $base)->whereYear('created_at', now()->subMonthNoOverflow()->year)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();
        if ($lastMonthCount > 0) {
            $newThisMonthTrendPct = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1);
        } else {
            $newThisMonthTrendPct = $thisMonthCount > 0 ? 100.0 : 0.0;
        }

        $stats = [
            'total' => $total,
            'total_trend_direction' => $newThisMonthTrendPct >= 0 ? 'up' : 'down',
            'total_trend_pct' => abs($newThisMonthTrendPct),
            'active' => $active,
            'active_pct' => $total > 0 ? round($active / $total * 100, 1) : 0,
            'inactive' => $inactive,
            'inactive_pct' => $total > 0 ? round($inactive / $total * 100, 1) : 0,
            'new_this_month' => $thisMonthCount,
            'new_this_month_trend_direction' => $newThisMonthTrendPct >= 0 ? 'up' : 'down',
            'new_this_month_trend_pct' => abs($newThisMonthTrendPct),
        ];

        return view('dashboard.pages.partners.index', compact('stats'));
    }

    // get index data by ajax
    public function get_data (Request $request) {
        $admins = Admin::with('user')->where('type', 'partner');

        if ($request->filled('filter_name')) {
            $admins->where('name', 'like', '%'.$request->filter_name.'%');
        }

        if ($request->filled('filter_email')) {
            $admins->where('email', 'like', '%'.$request->filter_email.'%');
        }

        if ($request->filled('filter_status') && in_array($request->filter_status, ['1', '0'], true)) {
            $status = $request->filter_status;
            $admins->whereHas('user', function ($query) use ($status) {
                $query->where('is_active', $status);
            });
        }

        if ($request->filled('filter_date_from')) {
            $admins->whereDate('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $admins->whereDate('created_at', '<=', $request->filter_date_to);
        }

        return Datatables::of($admins)
            ->editColumn('created_at', function(Admin $item) {
                return optional($item->created_at)->format('Y-m-d');
            })
            ->addColumn('commercial_register', function(Admin $item) {
                $path = $item->user->commercial_register ?? null;
                if (!$path) {
                    return '-';
                }
                return '<a href="' . Storage::disk('public')->url($path) . '" target="_blank">' . TranslationHelper::translate('commercial_register') . '</a>';
            })
            ->addColumn('status', function(Admin $item) {
                $isActive = $item->user->is_active ?? false;
                $badge = $isActive ? 'success' : 'danger';
                $label = $isActive ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive');
                return '<span class="badge rounded-pill bg-' . $badge . '">' . $label . '</span>';
            })
            ->addColumn('action', function(Admin $item) {
                return view('dashboard.pages.partners.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'email', 'national_id', 'commercial_register', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add partner');
        return view('dashboard.pages.partners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\StoreHospitalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePartnerRequest $request)
    {
        $this->authorizable('add partner');

        $commercialRegister = null;
        if ($request->hasFile('commercial_register')) {
            $commercialRegister = Storage::disk('public')->putFile('vendor-commercial-files', $request->file('commercial_register'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'user_name' => $request->user_name,
            'user_type' => 'vendor',
            'commercial_register' => $commercialRegister,
            'password' => bcrypt($request->password),
            // 'is_verified'=>$request->is_verified,
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('partners', $request->file('image')) : 'partners/default.png'

        ]);

        if ($request->is_verified == 'on') {
            $user->update([
                'is_verified' => 1
            ]);
        }


        // dd($user->id);




        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'type' => 'partner',
            'user_id'=>$user->id,
            'password' => bcrypt($request->password),
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('partners', $request->file('image')) : 'admins/default.png'
        ]);

        // dd($admin);
        Toastr::success(TranslationHelper::translate('New Partner Created Successfully'));
        return redirect()->route('admin.partners.index');
    }

    /**
     * Read-only details page for a single partner (linked from the "view"
     * icon in the partners table).
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $this->authorizable('view partners');
        $admin = Admin::with('user')->findorfail($id);

        return view('dashboard.pages.partners.show', compact(['admin']));
    }

    /**
     * Toggle the partner's active status. Partners are "Admin" records with
     * no "is_active" column of their own — the status shown/toggled is
     * actually the linked User record's (created alongside the Admin in
     * store(), via admin.user_id).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active_toogler($id)
    {
        $this->authorizable('edit partner');
        $admin = Admin::findorfail($id);
        $user = User::findorfail($admin->user_id);
        $this->trait_active_toogler($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {

        $this->authorizable('edit partner');
        $admin = Admin::findorfail($id);

        return view('dashboard.pages.partners.edit', compact(['admin']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\UpdateHospitalRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePartnerRequest $request, $id)
    {
        $this->authorizable('edit partner');
        $admin = Admin::findorfail($id);
        if ($request->hasFile('image') && $admin->image != 'partners/default.png' && $admin->image != NULL) {
            Storage::disk('public')->delete($admin->image);
        }
        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,

            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('partners', $request->file('image')) : $admin->image
        ]);


        $user = User::findorfail($admin->user_id);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'user_name' => $request->user_name,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'user_type' => 'vendor',
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('partners', $request->file('image')) : $user->image
        ];

        if ($request->hasFile('commercial_register')) {
            if ($user->commercial_register && Storage::disk('public')->exists($user->commercial_register)) {
                Storage::disk('public')->delete($user->commercial_register);
            }
            $userData['commercial_register'] = Storage::disk('public')->putFile('vendor-commercial-files', $request->file('commercial_register'));
        }

        $user->update($userData);
        if ($request->is_verified == 'on') {
            $user->update([
                'is_verified' => 1
            ]);
        }


        Toastr::success(TranslationHelper::translate('Partner Data Updated Successfully'));
        return redirect()->route('admin.partners.index');
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function change_password_form($id)
    {
        $this->authorizable('edit partner');
        $admin = Admin::findorfail($id);
        return view('dashboard.pages.partners.change_password', compact(['admin']));
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function save_password(ChangeAdminPasswordRequest $request, $id)
    {
        if ($id != Auth::guard('admin')->user()->id) {
            $this->authorizable('edit partner');
        }
        $admin = Admin::findorfail($id);
        $admin->update([
            'password' => bcrypt($request->password)
        ]);
        Toastr::success(TranslationHelper::translate('Partner Password Changed Successfully'));
        return redirect()->route('admin.partners.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete partner');
        $admin = Admin::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Partner Deleted Successfully'));
        return redirect()->back();
    }
}
