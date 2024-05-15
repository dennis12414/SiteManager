<?php

namespace App\Http\Controllers\ClockIns;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Worker;
use Illuminate\Http\Request;

class ClockInsController extends Controller
{


   public function clockIn(Request $request){

    $request->validate([
        'siteManagerId' => 'required|numeric',
        'projectId' => 'required|numeric',
        'workerId'=> 'required|numeric',
        'clockInTime' => 'required|date',
    ]);

    //check if worker is already clocked in
    $clockIn = ClockIns::where('workerId', $request->workerId )
                ->where('projectId', $request->projectId)
                ->where('clockInTime', $request->clockInTime)
                ->first();

    if ($clockIn) {
        return response([
            'message' => 'Worker already clocked in',
        ], 409);
    }


    $date = date('Y-m-d', strtotime($request->clockInTime));
    $clockIn = ClockIns::create([
        'siteManagerId' => $request->siteManagerId,
        'projectId' => $request->projectId,
        'workerId' => $request->workerId,
        'clockInTime' => $request->clockInTime,
        'date' => $date,
        'amountPaid' => null,
        'paymentStatus' => 'pending',
    ]);

    $createdClockIn = ClockIns::find($clockIn->clockId);

    $worker = Worker::where('workerId', $request->workerId)->first();

    $responseData = [
        'workerId' => $worker->workerId,
        'name' => $worker->name,
        'phoneNumber' => $worker->phoneNumber,
        'dateRegistered' => $worker->dateRegistered,
        'payRate' => $worker->payRate,
        'siteManagerId' => $worker->siteManagerId,
        'role' => $worker->role,
        'gender' => $worker->gender,
        'profilePic' => $worker->profilePic,
        'data' => $request->clockInTime,
        'clockIns' => [$createdClockIn],
    ];


    return response([
        'message' => 'Clocked in successfully',
        'data'=>$responseData,
    ], 201);

   }





   public function clockedInWorkers(Request $request){
    $request->validate([
        'siteManagerId' => 'required|numeric',
        'projectId' => 'required|numeric',
        'startDate' => 'date',
        'endDate' => 'date',

    ]);


    $clockIns = ClockIns::where('siteManagerId', $request->siteManagerId)
    ->where('projectId', $request->projectId)
    ->whereBetween('date', [$request->startDate, $request->endDate])
    ->get();



    //get worker details from worker table
    foreach($clockIns as $clockIn){
        $worker = Worker::where('workerId', $clockIn->workerId)->first();
        $clockIn->name = $worker->name;
        $clockIn->phoneNumber = $worker->phoneNumber;
        $clockIn->payRate = $worker->payRate;
    }



    if ($clockIns->isEmpty()) {
        return response([
            'message' => 'No workers clocked in',
        ], 404);
    }


    return response([
        'message' => 'Workers clocked in',
        'clockIns' => $clockIns,
    ], 200);

   }



   public function clockednotClocked(string $siteManagerId, string $projectId, string $startDate = null)
   {
        $startDate = request('startDate');


        $workers = Worker::where('siteManagerId', $siteManagerId)->get();
        $data = [];
        foreach ($workers as $worker) {
            $workerData = $worker->toArray(); // Convert worker object to array

            // Separate query to fetch clock-in details (if applicable)
            $clockInData = ClockIns::where('workerId', $worker->workerId)
                                    ->where('date', $startDate)
                                    ->where('projectId', $projectId)
                                    ->first();

            if ($clockInData) {
                $workerData['date'] = $clockInData->date;
                $workerData['clockInTime'] = $clockInData->clockInTime;
            } else {
                $workerData['date'] = null;
                $workerData['clockInTime'] = null;
            }

            // Remove unnecessary timestamps
            unset($workerData['created_at']);
            unset($workerData['updated_at']);
            unset($workerData['deleted_at']);

            $workerData['clockIns'] = []; // Initialize empty clockIns array

            // Check if clockInData exists and add details if necessary
            if ($clockInData) {
                $workerData['clockIns'][] = $clockInData->toArray(); // Add clockIn details as array
            }

            $data[] = $workerData;
        }

        //return json_encode($data);
        return response([
            'message' => 'fetched',
            'data'=>$data
        ], 200);


   }

   public function clockedInWorkerForPayments(string $siteManagerId, string $projectId, string $startDate = null, string $endDate = null, string $searchQuery = null){
        $startDate = request('startDate');
        $endDate = request('endDate');
        $searchQuery = request('searchQuery');

        if($startDate && $endDate){
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        }elseif($startDate){
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
            ->where('date', $startDate)
            ->get();
        }
        else{
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
            ->get();
        }

        if (!$clockIns) {
            return response([
                'message' => 'No workers clocked in',
            ], 404);
        }

        $totalDaysWorked = 0;
        $totalPay = 0;
        $clockInDates = [];

        //get worker details from worker table
        foreach($clockIns as $clockIn){
            $worker = Worker::where('workerId', $clockIn->workerId)->first();
            $clockIn->name = $worker->name;
            $clockIn->phoneNumber = $worker->phoneNumber;
            $clockIn->payRate = $worker->payRate;

            // Store clocked in dates for each worker
            $clockInDates[$worker->name][] = $clockIn->date;

            // Calculate total pay
            $totalPay += $worker->payRate;
        }


         if($searchQuery)
         {

            $clockIns = $clockIns->filter(function ($clockIn) use ($searchQuery) {
                if (strpos(strtolower($clockIn->name), strtolower($searchQuery)) !== false || strpos(strtolower($clockIn->phoneNumber), strtolower($searchQuery)) !== false) {
                    return true;
                }
            });

         }




   }


   public function clockedInWorker(string $siteManagerId, string $projectId, string $startDate = null, string $endDate = null, string $searchQuery = null)
   {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $searchQuery = request('searchQuery');

        if($startDate && $endDate){
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        }elseif($startDate){
              $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
              ->where('projectId', $projectId)
              ->where('date', $startDate)
              ->get();
        }
        else{
            $clockIns = ClockIns::where('siteManagerId', $siteManagerId)
            ->where('projectId', $projectId)
            ->get();
        }

        if (!$clockIns) {
            return response([
                'message' => 'No workers clocked in',
            ], 404);
        }

        //get worker details from worker table
        foreach($clockIns as $clockIn){
            $worker = Worker::where('workerId', $clockIn->workerId)->first();
            $clockIn->name = $worker->name;
            $clockIn->phoneNumber = $worker->phoneNumber;
            $clockIn->payRate = $worker->payRate;
            }


         if($searchQuery)
         {

            $clockIns = $clockIns->filter(function ($clockIn) use ($searchQuery) {
                if (strpos(strtolower($clockIn->name), strtolower($searchQuery)) !== false || strpos(strtolower($clockIn->phoneNumber), strtolower($searchQuery)) !== false) {
                    return true;
                }
            });

         }

        return response([
            //'option' => $option,
            'message' => 'Workers clocked in',
            'clockIns' => $clockIns,
        ], 200);


}


    public function undoClockedIn(Request $request)
    {
        $request->validate([
            'clockInId' => 'required|numeric',
        ]);

        $clockIn = ClockIns::find($request->clockInId);

        if (!$clockIn) {
            return response([
                'message' => 'Clock-in record not found',
            ], 404);
        }

        $clockIn->delete();

        return response([
            'message' => 'Clock-in record deleted successfully',
        ], 200);
    }





















}





