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
        // $transaction = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'];
        // $amount = $transaction[0]['Value'];
        // $mpesaReceiptNumber = $transaction[1]['Value'];
        // $transactionDate = $transaction[3]['Value'];
        // $phoneNumber = $transaction[4]['Value'];
        // $merchantRequestID = $transaction[5]['Value'];
        // $checkoutRequestID = $transaction[6]['Value'];
        // $resultCode = $transaction[7]['Value'];
        // $resultDesc = $transaction[8]['Value'];

        // $mpesaResponse = [
        //     'amount' => $amount,
        //     'mpesaReceiptNumber' => $mpesaReceiptNumber,
        //     'transactionDate' => $transactionDate,
        //     'phoneNumber' => $phoneNumber,
        //     'merchantRequestID' => $merchantRequestID,
        //     'checkoutRequestID' => $checkoutRequestID,
        //     'resultCode' => $resultCode,
        //     'resultDesc' => $resultDesc,
        // ];

        // return response([
        //     'message' => 'Mpesa response received',
        //     'mpesaResponse' => $mpesaResponse,
        // ], 200);

}
   


   

