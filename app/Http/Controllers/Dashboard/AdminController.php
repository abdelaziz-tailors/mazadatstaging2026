<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
class AdminController extends Controller
{
    use AuthorizeTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->authorizable('view admins');
        return view('dashboard.pages.admins.index');
    }

    // get index data by ajax
    public function get_data (Request $request) {
        $admins = Admin::where('type', 'admin');
        return Datatables::of($admins)
            ->addColumn('role', function(Admin $item) {
                if (count(json_decode($item->getRoleNames())) > 0)
                {
                    return json_decode($item->getRoleNames())[0];
                }
                else {
                    return NULL;
                }
            })
            ->addColumn('action', function(Admin $item) {
                return view('dashboard.pages.admins.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'email' ,'role', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add admin');
        $roles = Role::get();
        return view('dashboard.pages.admins.create', compact(['roles']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\StoreHospitalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdminRequest $request)
    {
        $this->authorizable('add admin');
        $role = Role::findorfail($request->role_id);
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => 'admin',
            'password' => bcrypt($request->password),
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('admins', $request->file('image')) : 'admins/default.png'
        ]);
        $admin->assignRole($role);
        Toastr::success(TranslationHelper::translate('New Administrator Created Successfully'));
        return redirect()->route('admin.admins.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit admin');
        $admin = Admin::findorfail($id);
        $roles = Role::get();
        return view('dashboard.pages.admins.edit', compact(['admin', 'roles']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\UpdateHospitalRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdminRequest $request, $id)
    {
        $this->authorizable('edit admin');
        $admin = Admin::findorfail($id);
        $role = Role::findorfail($request->role_id);
        $admin->roles()->detach();
        if ($request->hasFile('image') && $admin->image != 'admins/default.png' && $admin->image != NULL) {
            Storage::disk('public')->delete($admin->image);
        }
        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('admins', $request->file('image')) : $admin->image
        ]);
        $admin->assignRole($role);
        Toastr::success(TranslationHelper::translate('Administrator Data Updated Successfully'));
        return redirect()->route('admin.admins.index');
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function change_password_form($id)
    {
        $this->authorizable('edit admin');
        $admin = Admin::findorfail($id);
        return view('dashboard.pages.admins.change_password', compact(['admin']));
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
            $this->authorizable('edit admin');
        }
        $admin = Admin::findorfail($id);
        $admin->update([
            'password' => bcrypt($request->password)
        ]);
        Toastr::success(TranslationHelper::translate('Administrator Password Changed Successfully'));
        return redirect()->route('admin.admins.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete admin');
        $admin = Admin::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Administrator Deleted Successfully'));
        return redirect()->back();
    }
}
