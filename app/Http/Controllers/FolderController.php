<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Project;

class FolderController extends Controller
{
    public function index($project_id)
    {
        $folders = Folder::with('subfolders')->with('documents')->where('project_id', $project_id)->whereNull('parent_id')->get();
        return response()->json(['data' => $folders, 'message' => 'Folders retrieved successfully', 'status' => 200]);
    }

    public function store(Request $request, $parentId = null)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        $folder = Folder::where('name', $data['name'])->where('parent_id', $request->parent_id)->first();
        if ($folder) {
            $data['name'] = $data['name'] . ' (' . bin2hex(random_bytes(10)) . ')';
        }

        $folder = new Folder($data);
        $parentId = $request->parent_id;
        if ($parentId) {
            $parentFolder = Folder::find($parentId);
            if (!$parentFolder) {
                return response()->json([
                    'data' => "Not found",
                    'message' => 'No such folder found',
                    'status' => 404
                ], 404);
            }
        }

        if ($request->project_id) {
            $project = Project::find($request->project_id);
            if (!$project) {
                return response()->json([
                    'data' => "Not found",
                    'message' => 'No such project found',
                    'status' => 404
                ], 404);
            }
        }
        $folder->project_id = $request->project_id;
        $folder->parent_id = $request->parent_id;
        $folder->save();

        return response()->json(['data' => $folder, 'message' => 'Folder created successfully', 'status' => 201]);
    }

    public function show($id)
    {
        $folder = Folder::with('children')->find($id);

        if ($folder && !empty($folder)) {
            $folder->delete();
            return response()->json(['message' => 'Folder deleted successfully', 'status' => 200]);
        }
        return response()->json(['data' => $folder, 'message' => 'Folder retrieved successfully', 'status' => 200]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        $parentId = $request->parent_id;

        if ($request->project_id) {
            $project = Project::find($request->project_id);
            if (!$project) {
                return response()->json([
                    'data' => "Not found",
                    'message' => 'No such project found',
                    'status' => 404
                ], 404);
            }
        }

        if ($parentId) {
            $parentFolder = Folder::find($parentId);
            if (!$parentFolder) {
                return response()->json([
                    'data' => "Not found",
                    'message' => 'No such folder found',
                    'status' => 404
                ], 404);
            }
        }

        $folder = Folder::findOrFail($id);
        $folder->name = $data['name'];
        $folder->project_id = $request->project_id;
        $folder->parent_id = $request->parent_id;
        $folder->save();

        return response()->json(['data' => $folder, 'message' => 'Folder updated successfully', 'status' => 200]);
    }

    public function destroy($id)
    {
        $folder = Folder::find($id);

        if($folder->parent_id == null ){
            return response()->json(['message' => 'Root Folder Cannot Be Deleted', 'status' => 500]);
        }
        if ($folder && !empty($folder)) {
            $folder->delete();
            return response()->json(['message' => 'Folder deleted successfully', 'status' => 200]);
        }

        return response()->json([
            'data' => "Not found",
            'message' => 'No such folder found',
            'status' => 404
        ], 404);
    }
}
