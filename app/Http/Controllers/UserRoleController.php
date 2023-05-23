<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function setRole(Request $request, $id) {

        $user = User::where('id', $request->id)->first();

        if ($request['role_id'] != null) {
            $role = Role::where('id', $request['role_id'])->first();
            if(!$role){
                return response()->json(['error' => 'Role Not Found'], 422);
            }
            $user->roles()->detach();
            $user->roles()->attach($role, ['model_type' => 'App\Models\User']);
        } else {
            $user->roles()->detach();
        }
        return response()->json(['message' => 'Role Has Been Assigned Successfully', 'data' => $user], 200);

    }
}
