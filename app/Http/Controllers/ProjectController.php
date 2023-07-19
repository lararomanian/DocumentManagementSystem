<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();

        return response()->json([
            'data' => $projects,
            'message' => 'Successfully retrieved all projects',
            'status' => 200
        ], 200);
    }

    public function store(ProjectRequest $request)
    {

        $validator = Validator::make($request->all(),  $request->rules(), $request->messages());

        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'message' => 'Validation failed',
                'status' => 422
            ], 422);
        }

        try {
            $project = Project::create($request->all());
            return response()->json([
                'data' => $project,
                'message' => 'Successfully created project',
                'status' => 200
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => $th->getMessage(),
                'message' => 'Failed to create project',
                'status' => $th->getCode()
            ], $th->getCode());
        }
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

    public function update(ProjectRequest $request, Project $project)
    {
        try {
            $validator = Validator::make($request->all(),  $request->rules(), $request->messages());
            if ($validator->fails()) {
                return response()->json(["data" => "Validation Error", 'message' => $validator->errors(), "status" => 422], 422);
            }

            $project->update($request->all());
            return response()->json([
                'data' => $project,
                'message' => 'Successfully updated project',
                'status' => 200
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => $th->getMessage(),
                'message' => 'Failed to update project',
                'status' => $th->getCode()
            ], $th->getCode());
        }
    }

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


    public function addUser(Request $request, Project $project)
    {
        $user = User::findOrFail($request->user_id);
        $project->users()->attach($user);

        return response()->json(
            [
                'data' => $project,
                'message' => 'User added to the project successfully',
                'status' => 200
            ],
            200
        );
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
}
