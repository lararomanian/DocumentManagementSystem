<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserRoleResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends BaseCrudController
{


    public function __construct()
    {
        $this->query = (object) User::with("roles")->whereDoesntHave("roles", function ($q) {
            $q->whereIn("name", ['admin']);
        });

        $this->sort_term = "name";
    }

    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        if (count($this->with)) {
            $this->query->with(implode(',', $this->with));
        }

        if ($request->sort) {
            $this->sort($request->sort);
        }

        if ($request->search) {
            $this->search($request->search);
        }

        if ($request->filters) {
            $this->filter($request->filters);
        }

        return $this->returnResponse($per_page);
    }

    public function returnResponse($per_page)
    {
        return UserRoleResource::collection($this->query->orderBy('created_at', 'desc')->paginate($per_page)->appends(request()->query()));
    }

    public function search($search)
    {
        $this->query->where(function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function roles()
    {
        $roles = Role::where('name', '!=', 'admin')->where('is_deleted', false)->get(['id', 'name']);
        return response()->json($roles);
    }


    public function update(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        if ($request['role_id'] != null) {
            $role = Role::where('id', $request['role_id'])->first();
            $user->roles()->detach();

            // $user->assignRole($role->name);
            $user->syncRoles($role->name);

        } else {
            $user->roles()->detach();
        }

        return response()->json(['message' => 'Data Updated Successfully', 'data' => $user], 200);
    }
}
