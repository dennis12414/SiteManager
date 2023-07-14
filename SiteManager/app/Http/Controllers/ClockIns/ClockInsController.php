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

   
   
}
