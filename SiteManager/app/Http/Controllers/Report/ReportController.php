<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\SiteManager;
use App\Models\Worker;
use Carbon\Carbon;
use Hamcrest\Core\HasToString;
use Illuminate\Support\Facades\DB;
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

    //a lot of workers report
    public function getWorkerToPay(String $siteManagerId,String $projectId, string $startDate = null, string $endDate = null){

        $startDate = request('startDate');
        $endDate = request('endDate');


        if($startDate && $endDate){
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
                ->where('paymentStatus', '!=', 'paid')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        }elseif($startDate){
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
                ->where('paymentStatus', '!=', 'paid')
            ->where('date', $startDate)
            ->get();
        }
        else{
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
                ->where('paymentStatus', '!=', 'paid')
            ->where('projectId', $projectId)
            ->get();
        }

        if (!$clockIns) {
            return response([
                'message' => 'No workers clocked in',
            ], 404);
        }

         $uniqueUserIds = $clockIns->pluck('workerId')->unique()->values()->toArray();

         // Array to store return objects
         $returnObjects = [];

         // Loop through unique user IDs and call generateWorkerReport for each worker
         foreach ($uniqueUserIds as $userId) {
             $report = $this->generateWorkerReport($userId, $projectId, $startDate, $endDate);
             $returnObjects[] = $report;
         }

        return response([
            'message' => 'Retreived successfully',
            'data' => $returnObjects,
        ], 200);

    }




    //individual worker report
	public function generateWorkerReport($workerId, String $projectId, string $startDate = null, string $endDate = null)
	{
	    $worker = Worker::where('workerId', $workerId)->first();

	    if (!$worker) {
		return response([
		    'message' => 'Worker does not exist',
		], 404);
	    }

	    $clockIns = $this->getClockIns($workerId, $projectId, $startDate, $endDate);

	    if (!$clockIns->isEmpty()) {
		$workerDetails = $this->getWorkerDetails($worker);
		$workerData = $this->getWorkerData($clockIns, $worker);
		$totalWages = $this->getTotalWages($clockIns, $worker);

		return response([
		    'worker details' => $workerDetails,
		    'days worked' => $workerData,
		    'totalBalance' => $totalWages,
		], 200);
	    }

	    return response([
		'message' => 'No clock ins for this worker',
	    ], 404);
	}

	private function getClockIns($workerId, $projectId, $startDate, $endDate)
	{
	    $query = ClockIns::where('workerId', $workerId)
		->where('projectId', $projectId);

	    if ($startDate && $endDate) {
		$query->whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
	    } elseif ($startDate) {
		$query->where('date', [$startDate . ' 00:00:00', $startDate . ' 23:59:59']);
	    }

	    return $query->get();
	}

	private function getWorkerDetails($worker)
	{
	    return [
            'workerId'=>$worker->workerId,
            'name' => $worker->name,
		    'phoneNumber' => $worker->phoneNumber,
		    'payRate' => $worker->payRate,
            'profilePic'=>$worker->profilePic,
            'role'=>$worker->role,
		    'dateRegistered' => date('d-m-Y', strtotime($worker->dateRegistered)),
	    ];
	}

	private function getWorkerData($clockIns, $worker)
	{
	    $workerData = [];
	    $totalPaymentAmount = 0;
	    $totalWages = 0;

	    foreach ($clockIns as $clockIn) {
		if ($clockIn->clockInTime !== null) {
		    $totalPaymentAmount += $clockIn->amountPaid;
		    $totalWages += $worker->payRate;

		    $workerData[] = [
                'clockId'=>$clockIn->clockId,
		        'date' => $clockIn->date,
		        'totalPaidAmount' => $totalPaymentAmount,
		        'balance' => $worker->payRate - $clockIn->amountPaid,
		    ];
		}
	    }

	    return $workerData;
	}


	private function getTotalWages($clockIns, $worker)
	{
	    $totalWages = 0;

	    foreach ($clockIns as $clockIn) {
		if ($clockIn->clockInTime !== null) {
		    $totalWages += $worker->payRate;
		}
	    }

	    return $totalWages;
	}


    public function getBudget(String $projectId){
        $clockIns = ClockIns::where('projectId', $projectId)
            ->where('paymentStatus', 'paid')
            ->get();
        $total = 0;
        foreach($clockIns as $clockIn){
            $total = $total + $clockIn->amountPaid;
        }

        $project = Project::where('projectId', $projectId)->first();
        if(!$project){
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }

        return  response ([
            'message'=>'retrieved successfully',
            'spent'=>$total,
            'budget'=>$project->budget,
        ],200);

    }


    public function getClockInStats($projectId){


// Define the start and end dates for the past five weeks
        $startDate = Carbon::now()->subWeeks(5)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        // Query the clockIns table
        $clockIns = ClockIns::select(
            DB::raw('YEARWEEK(clockInTime, 1) as year_week'), // 1 to start weeks on Monday
            DB::raw('DAYOFWEEK(clockInTime) as day_of_week'),
            DB::raw('COUNT(*) as total_clock_ins')
        )
            ->where('projectId', $projectId)
            ->whereBetween('clockInTime', [$startDate, $endDate])
            ->groupBy('year_week', 'day_of_week')
            ->get();

        // Define the days of the week
        $daysOfWeek = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday'
        ];

        // Initialize the results array
        $results = [];

        // Loop through each week in the past five weeks
        for ($week = 0; $week < 5; $week++) {
            $currentWeek = Carbon::now()->subWeeks(4 - $week)->startOfWeek();
            $yearWeek = $currentWeek->format('oW'); // 'o' gives ISO-8601 year number, 'W' gives ISO-8601 week number

            // Initialize the days of the week for the current week to zero
            $results[$yearWeek] = [
                'week' => $yearWeek,
                'data' => [
                    'Monday' => 0,
                    'Tuesday' => 0,
                    'Wednesday' => 0,
                    'Thursday' => 0,
                    'Friday' => 0,
                    'Saturday' => 0
                ]
            ];
        }

        // Organize the results by week and day of the week
        foreach ($clockIns as $clockIn) {
            $yearWeek = $clockIn->year_week;
            $dayOfWeek = $daysOfWeek[$clockIn->day_of_week];
            if ($dayOfWeek !== 'Sunday') { // Exclude Sunday
                $results[$yearWeek]['data'][$dayOfWeek] = $clockIn->total_clock_ins;
            }
        }

        // Convert results to a list of weeks
        $weeks = array_values($results);
        $weeks = array_reverse($weeks);

        return  response ([
            'message'=>'retrieved successfully',
            'stats'=>$weeks,
        ],200);


    }


}
