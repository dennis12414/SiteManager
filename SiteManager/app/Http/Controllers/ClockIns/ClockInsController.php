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
    $clockIn = ClockIns::where('workerId', $request->workerId)
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
    ]);

   
    return response([
        'message' => 'Clocked in successfully',
    ], 201); 


    
   }

   public function clockedInWorkers(Request $request, string $date = null){
    $request->validate([
        'siteManagerId' => 'required|numeric', 
        'projectId' => 'required|numeric',
        'startDate' => 'date',
        'endDate' => 'date',
    ]);

    if($date){
        $clockIns = ClockIns::where('siteManagerId', $request->siteManagerId)
        ->where('projectId', $request->projectId)
        ->where('date', $date)
        ->get();
    }else{
        $clockIns = ClockIns::where('siteManagerId', $request->siteManagerId)
        ->where('projectId', $request->projectId)
        ->whereBetween('date', [$request->startDate, $request->endDate])
        ->get();
    }

    if ($clockIns->isEmpty()) {
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
    

    return response([
        'message' => 'Workers clocked in',
        'clockIns' => $clockIns,
    ], 200);


   }   
}
