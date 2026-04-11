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

use App\Models\Admin;
use Spatie\Permission\Models\Role;

use App\Http\Requests\Dashboard\Admin\StoreAdminRequest;
use App\Http\Requests\Dashboard\Admin\UpdateAdminRequest;
use App\Http\Requests\Dashboard\Admin\ChangeAdminPasswordRequest;
use App\Models\User\User;

class PartnerController extends Controller
{
    use AuthorizeTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->authorizable('view partners');
        return view('dashboard.pages.partners.index');
    }

    // get index data by ajax
    public function get_data (Request $request) {
        $admins = Admin::where('type', 'partner');
        return Datatables::of($admins)
            ->addColumn('action', function(Admin $item) {
                return view('dashboard.pages.partners.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'email', 'national_id', 'action'])
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'user_name' => $request->user_name,
            'user_type' => 'vendor',
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
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'user_name' => $request->user_name,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('partners', $request->file('image')) : $user->image
        ]);
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
