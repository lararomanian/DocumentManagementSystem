<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolePermissionRequest;
use App\Http\Resources\RolePermissionResource;
use App\Models\User;
use App\Traits\PermissionList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends BaseCrudController
{
    use PermissionList;
    public function __construct()
    {
        $this->query = (object) Role::where('name', '!=', 'admin');
        $this->request = new RolePermissionRequest();
        $this->sort_term = "name";
    }

    public function index(Request $request)
    {

        // return response()->json(gettype(Role::all()));
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
        return RolePermissionResource::collection($this->query->orderBy('created_at', 'desc')->where('is_deleted', false)->paginate($per_page)->appends(request()->query()));
    }

    public function search($search)
    {
        $this->query->where(function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function list()
    {
        $lists = $this->permissionList();
        return response()->json($lists);
    }



    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->request->rules(),
            $this->request->messages()
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            // return response()->json($request->all());
            $role = Role::create([
                'name' => $request['name'],
                'guard_name' => 'web'
            ]);

            $permissions = $this->getValidPermissions($request['permissions']);
            $role->syncPermissions($permissions);

            $this->setAdminRole();
            return response()->json(['message' => 'Data Created Successfully', 'data' => $role], 200);
        }
    }


    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->request->rules(),
            $this->request->messages()
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $role = Role::where('id', $request['id'])->first();
            $permissions = $this->getValidPermissions($request['permissions']);
            $role->syncPermissions($permissions);
            $role->name = $request['name'];
            $role->save();

            $this->setAdminRole();
            return response()->json(['message' => 'Data Updated Successfully', 'data' => $role], 200);
        }
    }

    public function abilities()
    {
        return auth()->user()->jsPermissions();
    }

    public function delete($id)
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
            return response()->json(['message' => 'Role Deleted !']);
        }
        return response()->json(['error' => 'Error Deleting Data !']);
    }

    private function getValidPermissions(array $permissions)
    {
        $validPermissions = [];
        foreach ($permissions as $permission) {
            $permission = Permission::where('name', $permission)->where('guard_name', 'web')->first();
            if ($permission) {
                $validPermissions[] = $permission;
            }
        }
        return $validPermissions;
    }

    public function setAdminRole()
    {
        $role = Role::where('name', 'admin')->first();

        $permissions = $this->permissionList();
        $role->syncPermissions($permissions);
    }
}
