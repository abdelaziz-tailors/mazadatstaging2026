<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Traits\ResponseTrait;

class RoleController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of all roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return $this->success_response(
            TranslationHelper::translate('roles_retrieved_successfully'),
            RoleResource::collection($roles)
        );
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|unique:roles,name',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'string',
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'admin',
        ]);

        if ($request->filled('permissions')) {
            foreach ($request->permissions as $permissionName) {
                $permission = Permission::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => 'admin',
                ]);
                $role->givePermissionTo($permission);
            }
        }

        return $this->success_response(
            TranslationHelper::translate('Role created successfully'),
            new RoleResource($role->load('permissions'))
        );
    }

    /**
     * Display the specified role.
     */
    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return $this->failed_response(
                TranslationHelper::translate('Role not found'),
                404
            );
        }

        return $this->success_response(
            TranslationHelper::translate('Role retrieved successfully'),
            new RoleResource($role)
        );
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->failed_response(
                TranslationHelper::translate('Role not found'),
                404
            );
        }

        $request->validate([
            'name'         => 'required|string|unique:roles,name,' . $id,
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'string',
        ]);

        $role->update(['name' => $request->name]);

        $role->syncPermissions([]);

        if ($request->filled('permissions')) {
            foreach ($request->permissions as $permissionName) {
                $permission = Permission::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => 'admin',
                ]);
                $role->givePermissionTo($permission);
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $this->success_response(
            TranslationHelper::translate('Role updated successfully'),
            new RoleResource($role->load('permissions'))
        );
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->failed_response(
                TranslationHelper::translate('Role not found'),
                404
            );
        }

        $role->syncPermissions([]);
        $role->delete();

        return $this->success_response(
            TranslationHelper::translate('Role deleted successfully')
        );
    }
}
