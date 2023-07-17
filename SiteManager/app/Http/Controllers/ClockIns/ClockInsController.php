<?php

namespace App\Http\Controllers\ClockIns;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
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


    //409 conflict
    //201 created
    //200 ok
    //404 not found
    //500 server error
    //401 unauthorized
    //403 forbidden
   


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

   public function clockedInWorkers(Request $request){
    $request->validate([
        'siteManagerId' => 'required|numeric', 
        'projectId' => 'required|numeric',
        'startDate' => 'required|date',
        'endDate' => 'required|date',
    ]);

    $clockIns = ClockIns::where('siteManagerId', $request->siteManagerId)
                ->where('projectId', $request->projectId)
                ->whereBetween('clockInTime', [$request->startDate, $request->endDate])
                ->get();

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
}
