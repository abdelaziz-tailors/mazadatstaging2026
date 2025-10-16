<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;

use App\Helpers\TranslationHelper;
use App\Traits\AuthorizeTrait;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Permissions as PermissionsList;

use App\Http\Requests\Dashboard\Role\StoreRoleRequest;
use App\Http\Requests\Dashboard\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    use AuthorizeTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->authorizable('view roles');
        return view('dashboard.pages.roles.index');
    }

    // get index data by ajax
    public function get_data (Request $request) {
        $roles = Role::get();
        return Datatables::of($roles)
            ->addColumn('action', function(Role $item) {
                return view('dashboard.pages.roles.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add role');
        $permissions = new PermissionsList;

        $permissions = $permissions->get_permissions();
        return view('dashboard.pages.roles.create', compact(['permissions']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Role\StoreRoleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoleRequest $request)
    {
        $this->authorizable('add role');
        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);
        if ($request->has('permission')) {
            for ($i = 0; $i < count($request->permission); $i++) {
                $permission_checker = Permission::where('name', $request->permission[$i])
                    ->where('guard_name', 'admin')->first();
                if ($permission_checker === NULL) {
                    Permission::create(['name' => $request->permission[$i], 'guard_name' => 'admin']);
                }
                $role->givePermissionTo($request->permission[$i]);
            }
        }
        Toastr::success(TranslationHelper::translate('New Role Created Successfully'));
        return redirect()->route('admin.roles.index');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->authorizable('edit role');
        $role = Role::findorfail($id);
        $permissions = new PermissionsList;

        $permissions = $permissions->get_permissions();

        return view('dashboard.pages.roles.edit', compact(['role', 'permissions']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        $this->authorizable('edit role');
        $role = Role::findorfail($id);
        $role->update(['name' => $request->name]);
        $role->syncPermissions([]);
        if ($request->has('permission')) {
            for ($i = 0; $i < count($request->permission); $i++) {
                $permission_checker = Permission::where('name', $request->permission[$i])
                    ->where('guard_name', 'admin')->first();
                if ($permission_checker === NULL) {
                    Permission::create(['name' => $request->permission[$i], 'guard_name' => 'admin']);
                }
                $role->givePermissionTo($request->permission[$i]);
            }
        }

        Toastr::success(TranslationHelper::translate('Role Updated Successfully'));
        return redirect()->route('admin.roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete role');
        $role = Role::findorfail($id);
        $role->syncPermissions([]);
        $role->delete();
        Toastr::success(TranslationHelper::translate('Role Deleted Successfully'));
        return redirect()->back();
    }
}
