<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\ProjectResource;
use App\Models\Folder;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProjectController extends BaseCrudController
{

    public function setup()
    {
        $this->model = new Project();
        $this->request = new ProjectRequest();
        $this->resource = new ProjectResource($this->model);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->request->rules(),
            $this->request->messages(),
            $this->request->attributes(),
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only($this->model->getFillable());
        $data = array_merge($data, $this->defaultData('create'));

        $role = $this->createProjectPermissions($data['name']);
        $item = $this->model->create($data);

        $initialFolder = new Folder([
            'name' => 'Root Folder',
            'parent_id' => null,
            'project_id' => $item->id, // Assuming you have a 'project_id' field in the Folder model
            'created_at' => now(),
        ]);
        $initialFolder->save();

        return response()->json(['message' => 'Data Created Successfully !!!', 'data' => $item], 200);
    }

    public function show(Project $project)
    {
        try {
            return response()->json([
                'data' => $project,
                'message' => 'Successfully retrieved project',
                'status' => 200
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => $th->getMessage(),
                'message' => 'Failed to retrieve project',
                'status' => $th->getCode()
            ], $th->getCode());
        }
    }

    // public function update(ProjectRequest $request, Project $project)
    // {
    //     try {
    //         $validator = Validator::make($request->all(),  $request->rules(), $request->messages());
    //         if ($validator->fails()) {
    //             return response()->json(["data" => "Validation Error", 'message' => $validator->errors(), "status" => 422], 422);
    //         }

    //         $project->update($request->all());
    //         return response()->json([
    //             'data' => $project,
    //             'message' => 'Successfully updated project',
    //             'status' => 200
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'data' => $th->getMessage(),
    //             'message' => 'Failed to update project',
    //             'status' => $th->getCode()
    //         ], $th->getCode());
    //     }
    // }

    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return response()->json([
                'data' => $project,
                'message' => 'Successfully deleted project',
                'status' => 200
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => $th->getMessage(),
                'message' => 'Failed to delete project',
                'status' => $th->getCode()
            ], $th->getCode());
        }
    }


    public function addUser(Request $request, $projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'data' => null,
                'message' => 'Project not found',
                'status' => 404
            ], 404);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'data' => null,
                'message' => 'User not found',
                'status' => 404
            ], 404);
        }

        // Assuming you want to add the user to the project_users many-to-many relationship
        $project->projectUsers()->attach($user->id);

        return response()->json([
            'data' => null,
            'message' => 'User added to project successfully',
            'status' => 200
        ], 200);
    }

    public function removeUser(Request $request, Project $project)
    {
        $user = User::findOrFail($request->user_id);
        $project->users()->detach($user);

        return response()->json(
            [
                'data' => $project,
                'message' => 'User removed from the project successfully',
                'status' => 200
            ],
            200
        );
    }

    public function getUsersInProject(Project $project)
    {
        try {

            $users = $project->users->map(function ($user) {
                return collect($user)->only(['id', 'name', 'email', 'created_at']);
            });


            return response()->json(
                [
                    'data' => $users,
                    'message' => 'Successfully retrieved users in project',
                    'status' => 200
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'data' => $th->getMessage(),
                    'message' => 'Failed to retrieve users in project',
                    'status' => $th->getCode()
                ],
                $th->getCode()
            );
        }
    }

    // public function getChilds(Request $request) {
    //     $project = Project::findOrFail($request->project_id);
    //     $folders = $project->folders;
    //     $documents = $project->documents;

    //     $folders = $folders->map(function ($folder) {
    //         return collect($folder)->only(['id', 'name', 'description', 'created_at']);
    //     });

    //     $documents = $documents->map(function ($document) {
    //         return collect($document)->only(['id', 'name', 'description', 'created_at']);
    //     });

    //     return response()->json(
    //         [
    //             'data' => [
    //                 'folders' => $folders,
    //                 'documents' => $documents
    //             ],
    //             'message' => 'Successfully retrieved childs',
    //             'status' => 200
    //         ],
    //         200
    //     );
    // }

    public function getChilds()
    {

        $projects = Project::all(['id', 'name', 'description', 'created_at']);
        return response()->json(
            [
                'data' => $projects,
                'message' => 'Successfully retrieved childs',
                'status' => 200
            ],
            200
        );
    }

    public function getAllProjects()
    {
        $projects = Project::where('status', 1)->get(['id', 'name']);
        return response()->json(
            [
                'data' => $projects,
                'message' => 'Successfully retrieved projects',
                'status' => 200
            ],
            200
        );
    }

    public function getAllProjectAndUsers()
    {

        $projects = Project::where('status', 1)->with('projectUsers')->get(['id', 'name']);

        //map the users to get only the pivot table data
        // $projects = $projects->map(function ($project) {
        //     $project->users = $project->projectUsers->map(function ($user) {
        //         return collect($user)->only(['id', 'name', 'email', 'created_at']);
        //     });
        //     return collect($project)->only(['id', 'name', 'users']);
        // });

        return response()->json(
            [
                'data' => $projects,
                'message' => 'Successfully retrieved projects',
                'status' => 200
            ],
            200
        );
    }

    public function createProjectPermissions($project)
    {
        $project = strtolower($project);
        $permissions = [
            $project . '.view',
            $project . '.create',
            $project . '.update',
            $project . '.delete',
        ];

        $createdPermissions = [];
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['guard_name' => 'web']
            );
            $createdPermissions[] = $permission;
        }

        $role = Role::first(['name' => 'admin']);
        $role->givePermissionTo($createdPermissions);
        return $createdPermissions;
    }
}
