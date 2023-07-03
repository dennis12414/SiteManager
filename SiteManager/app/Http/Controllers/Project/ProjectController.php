<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();
        return response([
            'message' => 'Retrieved successfully',
            'projects' => $projects,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string',
            'project_description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        //$site_manager_id = auth()->user()->id; //auth means authentication, user means the user who is currently logged in, id means the id of the user who is currently logged in

        auth()->user()->projects()->create([
            'project_name' => $request->project_name,
            'project_description' => $request->project_description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // $project = Project::create([
        //     'project_name' => $request->project_name,
        //     'project_description' => $request->project_description,
        //     'start_date' => $request->start_date,
        //     'end_date' => $request->end_date,
        //     'site_manager_id' => $site_manager_id,
        // ]);

        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
