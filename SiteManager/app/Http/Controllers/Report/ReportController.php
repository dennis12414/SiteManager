<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generateReport(String $projectId){

        $clockIns = ClockIns::where('projectId', $projectId)->get();
        if(!$clockIns){
            return response([
                'message' => 'No clock ins for this project',
            ], 404);
        }
        
        $project = Project::where('projectId', $projectId)->first();
        if(!$project){
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }
    
        $workerIds = $clockIns->pluck('workerId');
        $workers = Worker::whereIn('workerId', $workerIds)->get();
        if(!$workers){
            return response([
                'message' => 'No workers for this project',
            ], 404);
        }
    
        $workerData = [];
        foreach($workers as $worker){
            $totalDaysWorked = 0;
            foreach($clockIns as $clockIn){
                if($clockIn->workerId === $worker->workerId && $clockIn->clockInTime !== null){
                    $totalDaysWorked++;
                }
            }
            $totalWages = ($totalDaysWorked * $worker->payRate);
            $workerData[] = [
                'name' => $worker->name,
                'phoneNumber' => $worker->phoneNumber,
                'payRate' => $worker->payRate,
                'dateRegistered' => $worker->dateRegistered,
                'siteManagerId' => $worker->siteManagerId,
                'totalDaysWorked' => $totalDaysWorked,
                'totalWages' => $totalWages,
            ];
        }
    
        return response([
            'project' => $project ->only(['projectId','projectName', 'projectDescription']),
            'workers' => $workerData,
        ], 200);
        
    
    }

}
