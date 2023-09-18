<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\SiteManager;
use App\Models\Worker;
use PDF; 

class ReportController extends Controller
{
    public function generateReport(String $projectId,  string $startDate = null, string $endDate = null,string $date = null){
        $startDate = request('startDate');
        $endDate = request('endDate');

        //check if project exists
        $project = Project::where('projectId', $projectId)->first();
        if(!$project){
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }

        $choice = 0;
        if($startDate !== null && $endDate !== null){
            $startDate = $startDate . ' 00:00:00';
            $endDate = $endDate . ' 23:59:59';
            $clockIns = ClockIns::where('projectId', $projectId)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->get();
                        $choice = 1;
        }
        elseif($startDate){
            $clockIns = ClockIns::where('projectId', $projectId)
                        ->where('date', [$startDate . ' 00:00:00', $startDate . ' 23:59:59'])
                        ->get();
                        $choice = 2;
        }
        else{
            $clockIns = ClockIns::where('projectId', $projectId)->get();
            $choice = 5;
        }

        if(!$clockIns){
            return response([
                'message' => 'No clock ins for this project',
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
        $totalBalance = 0;
        foreach($workers as $worker){
            $totalDaysWorked = 0;
            $amountPaid = 0;
            $totalPaymentAmount = 0;
            $totalWages = 0;
            $balance = 0;
            
            foreach($clockIns as $clockIn){
                if($clockIn->workerId === $worker->workerId && $clockIn->clockInTime !== null){
                    $totalDaysWorked++;
                    $amountPaid += $worker->amountPaid;
                    $totalWages += $worker->payRate;
                    
                    if($clockIn->amountPaid !== null){
                        $totalPaymentAmount += $clockIn->amountPaid;
                    }
                }
            }
            $balance = $totalWages - $totalPaymentAmount;
            $workerData = [
                'name' => $worker->name,
                'phoneNumber' => $worker->phoneNumber,
                'payRate' => $worker->payRate,
                'dateRegistered' => date('d-m-Y', strtotime($worker->dateRegistered)),
                'totalDaysWorked' => $totalDaysWorked,
                'totalWages' => $totalWages,
                'paidAmount' => $totalPaymentAmount,
                'balance' => $balance,
            ]; 

            $totalBalance += $balance;


        }
        $projectData = [
            'Name' => $project->projectName,
            'Site Manager' => SiteManager::where('siteManagerId', $project->siteManagerId)->first()->name,
        ];

        //return json response
        return response([
            'project' => $projectData,
            'workers' => $workerData,
            'totalBalance' => $totalBalance,
        ], 200); 
        
    }

    //individual worker report
    public function generateWorkerReport(String $workerId,String $projectId,  string $startDate = null, string $endDate = null){
        $startDate = request('startDate');
        $endDate = request('endDate');
      
        $worker = Worker::where('workerId', $workerId)->first();
        if(!$worker){
            return response([
                'message' => 'Worker does not exist',
            ], 404);
        }

        if($startDate && $endDate){
            $startDate = $startDate . ' 00:00:00';
            $endDate = $endDate . ' 23:59:59';
            $clockIns = ClockIns::where('workerId', $workerId)
                        ->where('projectId', $projectId)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->get();
        }elseif($startDate){
            $clockIns = ClockIns::where('workerId', $workerId)
                        ->where('projectId', $projectId)
                        ->where('date', [$startDate . ' 00:00:00', $startDate . ' 23:59:59'])
                        ->get();
        }else{
            $clockIns = ClockIns::where('workerId', $workerId)
                ->where('projectId', $projectId)
                ->get();
        }

        if(!$clockIns){
            return response([
                'message' => 'No clock ins for this worker',
            ], 404);
        }

        //total days worked
        $totalDaysWorked = 0;
        $amountPaid = 0;
        $totalPaymentAmount = 0;
        $totalWages = 0;

        foreach($clockIns as $clockIn){
            if($clockIn->clockInTime !== null){
                $totalDaysWorked++;
                $amountPaid += $worker->amountPaid;
                //$totalWages += $worker->payRate;
                
                if($clockIn->amountPaid !== null){
                    $totalPaymentAmount == $clockIn->amountPaid;
                }
                $workerDetails = [
                    'name' => $worker->name,
                    'phoneNumber' => $worker->phoneNumber,
                    'payRate' => $worker->payRate,
                    'dateRegistered' => date('d-m-Y', strtotime($worker->dateRegistered)),
                ];

                $clockInTime[] = [
                    'clockInTime' => $clockIn->clockInTime,
                    'date' => $clockIn->date,
                ];
            
        
            $totalWages += $worker->payRate;
            $workerData [] = [
                'date' => $clockIn->date,
                'totalPaidAmount' =>  $totalPaymentAmount,
                'balance' => $worker->payRate - $clockIn->amountPaid,
            ];
         }
        }
        //personal details
        // $worker = [
        //     'name' => $worker->name,
        //     'phoneNumber' => $worker->phoneNumber,
        //     'payRate' => $worker->payRate,
        //     'dateRegistered' => date('d-m-Y', strtotime($worker->dateRegistered)),
        // ];

            return response([
                'worker details' => $workerDetails,
                'days worked' => $workerData,
                'totalBalance' => $totalWages ,
            ], 200);
        
    }

}
