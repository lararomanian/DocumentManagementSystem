<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function getAllRoles()
    {
        $roles = Role::where('is_deleted', false)->get(['id', 'name']);
        return response()->json(['roles' => $roles], 200);
    }

    public function getAllPermissions()
    {
        $permissions = Permission::all(['id', 'name']);
        $permissions = $permissions->groupBy(function ($item, $key) {
            return explode('.', $item['name'])[0];
        });
        return response()->json(['permissions' => $permissions], 200);
    }


    public function createRole(Request $request)
    {


        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        $permissions = $request->permissions;

        foreach ($permissions as $permission) {
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
        return response()->json(['message' => 'Role created successfully'], 200);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $role = Role::where('id', $id)->first();
        if(!$role){
            return response()->json(['error' => 'Role Not Found'], 422);
        }
        $role->syncPermissions($request->permissions);
        $role->name = $request['name'];
        $role->save();

        return response()->json(['message' => 'Data Updated Successfully', 'data' => $role], 200);
    }

    public function deleteRole($id)
    {
        $data = Role::find($id);
        $users = User::all();

        if (!$data) {
            return response()->json(['error' => 'Bad Request !!'], 404);
        } else {
            foreach ($users as $user) {
                if ($user->hasRole($data->name)) {
                    $user->removeRole($data->name);
                }
            }
            $data->is_deleted = true;
            $data->save();

            $data->delete();
            return response()->json(['message' => 'Role Deleted !']);
        }
        return response()->json(['error' => 'Error Deleting Data !']);
    }

}
