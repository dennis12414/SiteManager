<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use PDF; 

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
                'dateRegistered' => date('d-m-Y', strtotime($worker->dateRegistered)),
                'siteManagerId' => $worker->siteManagerId,
                'totalDaysWorked' => $totalDaysWorked,
                'totalWages' => $totalWages,
            ]; //
        } 
        

        //save the report in a csv file and send it as an attachment
        $fileName = 'report.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

    
        $columns = array('Name', 'Phone Number', 'Pay Rate', 'Date Registered', 'Total Days Worked', 'Total Wages');
        $callback = function() use ($workerData, $columns){
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach($workerData as $worker){
                fputcsv($file, array($worker['name'], $worker['phoneNumber'], $worker['payRate'], $worker['dateRegistered'], $worker['totalDaysWorked'], $worker['totalWages']));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);

        
    
      
    
    }

}
