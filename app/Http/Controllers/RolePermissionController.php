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
        try {
            $roles = Role::where('is_deleted', false)->get(['id', 'name']);
            return response()->json(['data' => $roles, "message" => "All roles fetched successfully", "status" => 200], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }

    public function getAllPermissions()
    {
        try {
            $permissions = Permission::all(['id', 'name']);
            $permissions = $permissions->groupBy(function ($item, $key) {
                return explode('.', $item['name'])[0];
            });
            return response()->json(['data' => $permissions, "message" => "All Permissions fetched successfully", "status" => 200], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }


    public function createRole(Request $request)
    {

        try {
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
            return response()->json(["data" => $role, 'message' => 'Role created successfully', "status" => 200],200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }

    public function updateRole(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required'
            ]);

            $role = Role::where('id', $id)->first();
            if (!$role) {
                return response()->json(["data" => "An Error Occurred",'message' => 'Role Not Found', "status" => 204], 204);
            }
            $role->syncPermissions($request->permissions);
            $role->name = $request['name'];
            $role->save();

            return response()->json(['message' => 'Data Updated Successfully', 'data' => $role, "status" => 200], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }

    public function deleteRole($id)
    {
        try {
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
                return response()->json(["data" => "Role deleted successfully", 'message' => 'Role deleted successfully', "status" => 200], 200);
            }
            return response()->json(['data' => 'Error Deleting Data !', "message" => "Error Deleting Data !", "status" => 500], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }
}
