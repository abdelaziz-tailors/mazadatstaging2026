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
        return view('dashboard.pages.vendors.index',compact('request'));
    }


    // get index data by ajax
    public function get_data (Request $request) {
        // dd($re/)


        $admin_id=Auth::guard('admin')->user()->id;

        $providers = User::where('admin_id',$admin_id)->where('user_type','vendor');


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

        return view('dashboard.pages.vendors.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\StoreProviderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVendorRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'admin_id' => Auth::guard('admin')->user()->id,
            'user_type'=>'vendor'


        ]);
        Toastr::success(TranslationHelper::translate(' Created Successfully'));
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
        if ($user->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }

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
        if ($provider->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }

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
