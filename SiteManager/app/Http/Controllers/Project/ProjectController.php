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
        
        //check if site manager exists
        $siteManager = SiteManager::where('siteManagerId', $request->siteManagerId)->first();
        if (!$siteManager) {
            return response([
                'message' => 'Site Manager does not exist',
            ], 404);
        }

        //create project
        $project = Project::create([
            'siteManagerId' => $request->siteManagerId,
            'projectName' => $request->projectName,
            'projectDescription' => $request->projectDescription,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

      
        return response([
            'message' => 'Project created successfully',
        ], 201);
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $projects = Project::where('siteManagerId', $id)->get();


        if ($projects->isEmpty()) {
            return response([
                'message' => 'No projects found',
            ], 404);
        }

        return response([
            'message' => 'Retrieve successfully',
            'project' => $projects->map(function($project){
                return $project->only(['projectId','siteManagerId','projectName', 'projectDescription', 'startDate', 'endDate']);
            })
        ], 200);
        
    }

    public function details(string $id)
    {
        //show a project where projectId = $id
        $project = Project::where('projectId', $id)->first();
        if (!$project) {
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }

        return response([
            'message' => 'Retrieved successfully',
            'project' => $project->only(['projectId','siteManagerId','projectName', 'projectDescription', 'startDate', 'endDate'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'siteManagerId' => 'required|numeric', 
            'projectName' => 'required|string',
            'projectDescription' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);
        
        //check if site manager exists
        $siteManager = SiteManager::where('siteManagerId', $request->siteManagerId)->first();
        if (!$siteManager) {
            return response([
                'message' => 'Site Manager does not exist',
            ], 404);
        }

        //update project
        $project = Project::where('siteManagerId', $id)->update([
            'siteManagerId' => $request->siteManagerId,
            'projectName' => $request->projectName,
            'projectDescription' => $request->projectDescription,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

      
        return response([
            'message' => 'Project updated successfully',
        ], 201);
        
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function archive(string $projectId, string $siteManagerId)
    {
        $project =  Project::where('projectId', $projectId)
                            ->where('siteManagerId', $siteManagerId)
                            ->first();

        if (!$project) {
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }

        $project->delete();

        return response([
            'message' => 'Project archived successfully',
        ], 200);

        
    }
}
