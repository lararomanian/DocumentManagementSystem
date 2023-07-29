<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function getHomeProjects() {

        $projects = Project::where('status', 1)->get();

        $projects = $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'documents' => $project->documents[0]->path ?? null,
                // 'document_count' => $project->getAllDocumentCount(),
             ];
        });

        return response()->json([
            'projects' => $projects,
        ]);
    }
}
