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

    $date = date('Y-m-d');
    //check if site manager has already clocked in for the day
    $clockIn = ClockIns::where('workerId', $request->workerId)->whereDate('date', $date)->first();
    if ($clockIn) {
        return response([
            'message' => 'Worker already clocked in',
        ], 401);
    }

    //create clock in
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

   
   public function clockOut(Request $request){

        $request->validate([
            'siteManagerId' => 'required|numeric', 
            'projectId' => 'required|numeric',
            'workerId'=> 'required|numeric',
            'clockOutTime' => 'required|date',
        ]);

        $date = date('Y-m-d');

        //check if site manager has already clocked out for the day
        $clockOut = ClockIns::where('workerId', $request->workerId)->whereDate('date', $date)->first();
        if (!$clockOut) {
            return response([
                'message' => 'Worker has not clocked in',
            ], 401);
        }
      
        if ($clockOut->clockOutTime) {
            return response([
                'message' => 'Worker already clocked out',
            ], 401);
        }

        
        $clockId = $clockOut->clockId;

        //update clock out
        $clockOut = ClockIns::where('clockId', $clockId)->update([
            'clockOutTime' => $request->clockOutTime,
        ]);

        return response([
            'message' => 'Clocked out successfully',
        ], 201);
        
        
    }
}
