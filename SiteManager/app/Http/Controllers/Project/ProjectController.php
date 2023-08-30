<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SiteManager;
use Illuminate\Http\Request;


class ProjectController extends Controller
{
    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'siteManagerId' => 'required|numeric',
            'projectName' => 'required|string',
            'projectDescription' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        $siteManager = SiteManager::find($request->siteManagerId);
        if (!$siteManager) {
            return response(['message' => 'Site Manager not found'], 404);
        }

        Project::create([
            'siteManagerId' => $request->siteManagerId,
            'projectName' => $request->projectName,
            'projectDescription' => $request->projectDescription,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        return response([
            'message' => 'Project created successfully'
        ], 201);
    }

    /**
     * Display a list of projects for a specific site manager.
     */
    public function show(string $siteManagerId)
    {
        $projects = Project::where('siteManagerId', $siteManagerId)->get();

        if ($projects->isEmpty()) {
            return response(['message' => 'No projects found'], 404);
        }

        $projectData = $projects->map(function ($project) {
            return $project->only(['projectId', 'siteManagerId', 'projectName', 'projectDescription', 'startDate', 'endDate']);
        });

        return response([
            'message' => 'Retrieved successfully', 
            'projects' => $projectData
        ], 200);
    }

    /**
     * Display details of a specific project.
     */
    public function details(string $projectId)
    {
        $project = $this->findProject($projectId);

        return response([
            'message' => 'Retrieved successfully',
            'project' => $project->only(['projectId', 'siteManagerId', 'projectName', 'projectDescription', 'startDate', 'endDate'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $projectId)
    {
        $request->validate([
            'projectName' => 'string',
            'projectDescription' => 'string',
            'startDate' => 'date',
            'endDate' => 'date',
        ]);

        $project = $this->findProject($projectId);

        $project->update([
            'projectName' => $request->projectName,
            'projectDescription' => $request->projectDescription,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        return response([
            'message' => 'Project updated successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function archive(string $projectId)
    {
        $project = $this->findProject($projectId);
        $project->delete();

        return response([
            'message' => 'Project archived successfully'
        ], 200);
    }

    /**
     * Find a project by its projectId.
     */
    private function findProject($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response([
                'message' => 'Project does not exist'
            ], 404);
        }

        return $project;
    }
}
