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
            $clockIns = ClockIns::where('projectId', $projectId)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->get();
                        $choice = 1;
        }
        elseif($startDate){
            $clockIns = ClockIns::where('projectId', $projectId)
                        ->where('date',$startDate)
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
            $workerData[] = [
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

        // if($reportType === 'pdf'){
        //     //well formated pdf
        //     // $pdf = PDF::loadView('report', [
        //     //     'project' => $projectData,
        //     //     'workers' => $workerData,
        //     //     'totalBalance' => $totalBalance,
        //     // ]);
        //     //return $pdf->download('report.pdf');
        // }

        // elseif($reportType === 'csv'){
        //     $headers = array(
        //         "Content-type" => "text/csv",
        //         "Content-Disposition" => "attachment; filename=report.csv",
        //         "Pragma" => "no-cache",
        //         "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        //         "Expires" => "0"
        //     );

        //     $columns = array('Name', 'Phone Number', 'Pay Rate', 'Date Registered', 'Total Days Worked', 'Total Wages', 'Paid Amount', 'Balance');

        //     $callback = function() use ($workerData, $columns)
        //     {
        //         $file = fopen('php://output', 'w');
        //         fputcsv($file, $columns);

        //         foreach($workerData as $worker) {
        //             fputcsv($file, array($worker['name'], $worker['phoneNumber'], $worker['payRate'], $worker['dateRegistered'], $worker['totalDaysWorked'], $worker['totalWages'], $worker['paidAmount'], $worker['balance']));
        //         }
        //         fclose($file);
        //     };
        //     return response()->stream($callback, 200, $headers);

        // }
        
           //return json response
            return response([
                'project' => $projectData,
                'workers' => $workerData,
                'totalBalance' => $totalBalance,

            ], 200); 
        

    }

    //individual worker report
    public function generateWorkerReport(String $workerId,  string $startDate = null, string $endDate = null,string $date = null){
        $startDate = request('startDate');
        $endDate = request('endDate');
      
        $worker = Worker::where('workerId', $workerId)->first();
        if(!$worker){
            return response([
                'message' => 'Worker does not exist',
            ], 404);
        }

        if($startDate && $endDate){
            $clockIns = ClockIns::where('workerId', $workerId)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->get();
        }elseif($startDate){
            $clockIns = ClockIns::where('workerId', $workerId)
                        ->where('date', $startDate)
                        ->get();
        }else{
            $clockIns = ClockIns::where('workerId', $workerId)->get();
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
        $balance = 0;

        foreach($clockIns as $clockIn){
            if($clockIn->clockInTime !== null){
                $totalDaysWorked++;
                $amountPaid += $worker->amountPaid;
                //$totalWages += $worker->payRate;
                
                if($clockIn->amountPaid !== null){
                    $totalPaymentAmount == $clockIn->amountPaid;
                }
                $workered[] = [
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
                //'name' => $worker->name,
                'date' => $clockIn->date,
                //'clockInTime' => $clockIn->clockInTime,
                //'phoneNumber' => $worker->phoneNumber,
                //'payRate' => $worker->payRate,
                //'dateRegistered' => date('d-m-Y', strtotime($worker->dateRegistered)),
                //'siteManagerId' => $worker->siteManagerId,
                //'totalDaysWorked' => $totalDaysWorked,
                //'totalWages' => $totalWages,
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
                //'start date' => $startDate,
                //'end date' => $endDate,
                'worker details' => $workered[0],
                'days worked' => $workerData,
                 'totalBalance' => $totalWages ,
            ], 200);
        




    }

}
