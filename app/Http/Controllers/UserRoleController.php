<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function setRole(Request $request, $id)
    {

        try {
            $user = User::where('id', $request->id)->first();

            if ($request['role_id'] != null) {
                $role = Role::where('id', $request['role_id'])->first();
                if (!$role) {
                    return response()->json(['data' => 'Role Not Found', 'message' => 'Role Not Found', "status" => 204], 204);
                }
                $user->roles()->detach();
                $user->roles()->attach($role, ['model_type' => 'App\Models\User']);
            } else {
                $user->roles()->detach();
            }
            return response()->json(['message' => 'Role Has Been Assigned Successfully', 'data' => $user, "status" => 200], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }
}
