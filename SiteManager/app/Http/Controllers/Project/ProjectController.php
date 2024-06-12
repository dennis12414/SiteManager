<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SiteManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


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
            'progress' => 'required|Integer',
            'budget' => 'required|string',
            'status' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        //check if site manager exists
        $siteManager = SiteManager::where('siteManagerId', $request->siteManagerId)->first();
        if (!$siteManager) {
            return response([
                'message' => 'Site Manager does not exist',
            ], 404);
        }


        if($request->file('image') != null){
            $uuid = Str::uuid();

            $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
            if (!$imageFolderPath) {
                return response([
                    'ty' => $imageFolderPath,
                    'message' => 'Image folder path not found in settings',
                ], 500);
            }
            $domain = Setting::where('setting_key', 'domain')->value('value');


            $uploadedImage = $request->file('image');


            $imageName = $uuid . '_' . $uploadedImage->getClientOriginalName();


            $uploadedImage->move($imageFolderPath->value, $imageName);
            $inviteCode = Str::uuid()->toString();

            $project = Project::create([
                'projectName' => $request->projectName,
                'projectDescription' => $request->projectDescription,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'status' => $request->status,
                'budget' => $request->budget,
                'progress' => $request->progress,
                'image' => $imageName,
                'inviteCode'=>$inviteCode
            ]);

            $siteManager = SiteManager::findOrFail($request->siteManagerId);
            $project->siteManagers()->attach($siteManager->siteManagerId);

            $project->refresh();
            $project['siteManagerId'] = (int) $request->siteManagerId;

            $domain = Setting::where('setting_key', 'domain')->value('value');

            $project->image = $domain . '/' .'api/images'. '/' . $project->image_name;

            return response([
                'message' => 'Project created successfully',
                'project'=> $project->only(['projectId','siteManagerId','projectName', 'projectDescription', 'startDate', 'endDate','progress','status','image','inviteCode'])
            ], 201);


        }else{
            $inviteCode = Str::uuid()->toString();
            $project = Project::create([
                'projectName' => $request->projectName,
                'projectDescription' => $request->projectDescription,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'status' => $request->status,
                'budget' => $request->budget,
                'progress' => $request->progress,
                'image' => null,
                'inviteCode'=>$inviteCode
            ]);

            $siteManager = SiteManager::findOrFail($request->siteManagerId);
            $project->siteManagers()->attach($siteManager->siteManagerId);

            $project->refresh();
            $project['siteManagerId'] = (int) $request->siteManagerId;

            return response([
                'message' => 'Project created successfully',
                'project'=> $project->only(['projectId','siteManagerId','projectName', 'projectDescription', 'startDate', 'endDate','progress','status','image','inviteCode'])
            ], 201);
        }





    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $siteManager = SiteManager::with('projects')->findOrFail($id);
        $projects = $siteManager->projects;
        if ($projects->isEmpty()) {
            return response([
                'message' => 'No projects found',
            ], 404);
        }
        $domain = Setting::where('setting_key', 'domain')->value('value');


        foreach($projects as $project){
            $project['siteManagerId'] = (int) $id;
            if($project->image != null){
                $project->image = $domain . '/' .'api/images'. '/' . $project->image;
            }
        }
        return response([
            'message' => 'retrieved success',
            'project' => $projects->map(function($project){
                return $project->only(['projectId','siteManagerId','projectName','budget', 'projectDescription', 'startDate', 'endDate','progress','status','image','inviteCode']);
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
            'project' => $project->only(['projectId','siteManagerId','projectName','budget', 'projectDescription', 'startDate', 'endDate','inviteCode'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $projectId,string $userId)
    {
        $request->validate([
            'projectName' => 'required|string',
            'projectDescription' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'budget' => 'required|string',
            'progress' => 'required|string',
            'status' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
//
        //check if site manager exists
        $project = Project::where('projectId', $request->projectId)->first();
        if (!$project) {
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }


        if($request->file('image') != null) {
            $uuid = Str::uuid();

            $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
            if (!$imageFolderPath) {
                return response([
                    'ty' => $imageFolderPath,
                    'message' => 'Image folder path not found in settings',
                ], 500);
            }
            $domain = Setting::where('setting_key', 'domain')->value('value');


            $uploadedImage = $request->file('image');


            $imageName = $uuid . '_' . $uploadedImage->getClientOriginalName();


            $uploadedImage->move($imageFolderPath->value, $imageName);

            $project = Project::where('projectId', $projectId)->update([
                'projectName' => $request->projectName,
                'projectDescription' => $request->projectDescription,
                'startDate' => $request->startDate,
                'budget' => $request->budget,
                'endDate' => $request->endDate,
                'status' => $request->status,
                'progress' => $request->progress,
                'image' => $imageName,
            ]);

            $project = Project::where('projectId', $request->projectId)->first();
            $project->refresh();
            $project['siteManagerId'] = (int) $userId;

            $domain = Setting::where('setting_key', 'domain')->value('value');

            $project->image = $domain . '/' .'api/images'. '/' . $project->image;

            return response([
                'message' => 'Project updated successfully',
                'project'=> $project->only(['projectId','siteManagerId','projectName','budget','projectDescription', 'startDate', 'endDate','progress','status','image','inviteCode'])
            ], 201);


        }else{
            $project = Project::where('projectId', $projectId)->update([
                'projectName' => $request->projectName,
                'projectDescription' => $request->projectDescription,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'status' => $request->status,
                'budget' => $request->budget,
                'progress' => $request->progress,
            ]);

            $project = Project::where('projectId', $request->projectId)->first();
            $project->refresh();
            $project['siteManagerId'] = (int) $userId;

            $domain = Setting::where('setting_key', 'domain')->value('value');

            $project->image = $domain . '/' .'api/images'. '/' . $project->image_name;

            return response([
                'message' => 'Project updated successfully',
                'project'=> $project->only(['projectId','siteManagerId','projectName', 'budget','projectDescription', 'startDate', 'endDate','progress','status','image','inviteCode'])
            ], 201);

        }
            //update project
    }

    /**
     * Remove the specified resource from storage.
     */
    public function archive(string $projectId)
    {
        $project =  Project::where('projectId', $projectId)
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


    //add member to project
    public function addMember(String $userId, String $inviteCode){

        $project = Project::where('inviteCode', $inviteCode)->first();
        if (!$project) {
            return response([
                'message' => 'InviteCode not found',
            ], 404);
        }
        $siteManager = SiteManager::findOrFail($userId);

        if (!$project->siteManagers->contains($siteManager)) {
            $project->siteManagers()->attach($siteManager->siteManagerId);
        }else{
            return response([
                'message' => 'InviteCode not found',
            ], 409);
        }

        $project['siteManagerId'] = (int) $userId;
        return response([
            'message'=>'retrived',
            'project'=>$project->only(['projectId','siteManagerId','projectName', 'projectDescription', 'startDate', 'endDate','inviteCode'])
        ],200);


    }

    public function getMembers(String $projectId){

        $project = Project::where('projectId', $projectId)->first();
        if (!$project) {
            return response([
                'message' => 'InviteCode not found',
            ], 404);
        }

        $members = $project->siteManagers;

        return response([
            'message'=>'retrived',
            'project'=>  $members->map(function($member){
                return $member->only(['siteManagerId','name', 'email', 'phoneNumber', 'dateRegistered','profilePicture']
                    ,200);
            })
        ]);


    }

}
