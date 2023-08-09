<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use App\Models\SiteManagerWallet;
use App\Models\SiteManager;
use Illuminate\Http\Request;


class B2CCntroller extends Controller
{
    public function initiatePayment(Request $request){
        $request->validate([
            'siteManagerId' => 'required|numeric',
            'projectId'=> 'required|numeric',
            'workerId' => 'required|numeric',
            'date' => 'date'
        ]);

        $siteManager = SiteManager::find($request->siteManagerId);
        if(!$siteManager){return response([ 'message' => 'Site Manager does not exist', ], 404);}

        $project = Project::find($request->projectId);
        if(!$project){ return response([ 'message' => 'Project does not exist', ], 404);}

        $worker = Worker::find($request->workerId);
        if(!$worker){return response(['message' => 'Worker does not exist',], 404);}

        $clockIn = ClockIns::where('projectId', $request->projectId)
                            ->where('workerId', $request->workerId)
                            ->where('clockInTime', $request->date)
                            ->first();
        if(!$clockIn){
            return response([
                'message' => 'Worker did not clock in for this day',
            ], 404);
        }

        //check worker pay rate
        $totalAmount = $worker->payRate;
        $phoneNumber = $worker->phoneNumber;


        //check if worker has been paid
        $paid = $clockIn->amountPaid;
        if($paid){
            return response([
                'message' => 'Worker has already been paid',
            ], 404);
        }
        
        //check if site manager has enough money in their wallet
        $wallet = SiteManagerWallet::where('siteManagerId', $request->siteManagerId)->first();
        if(!$wallet){ return response([ 'message' => 'Site Manager does not have a wallet', ], 404);}

        $balance = $wallet->balance;
        if($balance < $totalAmount){
            return response([
                'message' => 'Insufficient funds',
                'funds' => $balance,
            ], 404);
        }

        $wallet->heldBalance += $totalAmount;
        $wallet->save();

        $paymentDetails = [
            ['phoneNumber' => $phoneNumber, 'amount' => $totalAmount]
        ];
        
        //inititiate payment 
        $result = $this->bulkPayment($paymentDetails);

        return response([
            'message' => 'Payment pending processing',
            'data' => $result,
        ], 200);

        //payment details
        // $paymentDetails = [];
        // foreach ($request->workers as $worker) {
        //     //$transactionReference = uniqid('payment_');
        //     $paymentDetails[] = [
        //         'phoneNumber' => $worker['phoneNumber'],
        //         'amount' => $worker['payRate'],
        //         //'transactionReference' => $transactionReference,
        //     ];

        //     //save the transaction reference in clock ins
        //     // $clockIn = ClockIns::where('projectId', $request->projectId)
        //     //                 ->where('workerId', $request->workerId)
        //     //                 ->where('clockInTime', $request->date)
        //     //                 ->first();
                            
        //     // $clockIn->transactionReference = $transactionReference;
        //     // $clockIn->save();
        // }

        //inititiate payment 
        //$result = $this->bulkPayment($paymentDetails);

        // return response([
        //     'message' => 'Payment pending processing',
        //     'data' => $result,
        // ], 200);
        
    }   

public function bulkPayment($paymentDetails)
{
    $url = "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";
    $pass = "Safaricom999!*!"; 
    
    $SecurityCredential = "Z2tWOKg2yaHdhH9aXcTQ3UeH3ZRANcJtEtLGmJRzJHrlNFv2oxk4XjRNuUy8ujnrTL2+4wDkTnof7IHgttPYDHwSVFImvcyanaZXg9bZkgBq9UhEQAIH68XG0MRL4jZ0UUcSOI9Lm6TU51imUFjxyXmTLzcQoUP/WrWXn4iw5S686UA2gBxoyrFX3E/VF6AhNU5cYvHJHvpPxETlcZb7IUX0XlHOvE35S7yOSOXADObzHYzyeB7kYNSuidDD3YojKV9zm4Ysu9BCErCcHz+drfgkmNTAC7hBMOQ7h6QLJ/rXI6iQBitiniFufv4D+6eAhqTvuTwIChBdicbSrLAS4g==";
    //openssl_public_encrypt($pass, $encrypted, $publickey, OPENSSL_PKCS1_PADDING);
    //$SecurityCredential = base64_encode($encrypted);
    foreach ($paymentDetails as $payment) {
        $phoneNumber = $payment['phoneNumber'];
        $amount = $payment['amount'];
    }
    if(substr($phoneNumber, 0, 1) == '0'){ 
          $phoneNumber = '254' . substr($phoneNumber, 1);
    }
    $phoneNumber = str_replace('+', '', $phoneNumber);
    $phoneNumber = str_replace(' ', '', $phoneNumber);


    $data =  array(  
          "InitiatorName" => "testapi",
          "SecurityCredential"=> $SecurityCredential,
          "CommandID"=>"SalaryPayment",
          "Amount"=> $amount,
          "PartyA"=> 600983, 
          "PartyB"=> "254708374149",
          "Remarks"=> "Salary payment",
          "QueueTimeOutURL"=> env('APP_URL') . '/api/b2c/timeout',
          "ResultURL"=> env('APP_URL') . '/api/result',
          "Occassion"=>"Salary payment"
    );

    $data_string = json_encode($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->getAccessToken())); //setting custom header
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    $curl_response = curl_exec($curl);
    $response = json_decode($curl_response);
    return $response;

    // $url = "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";
    // $pass = "Safaricom999!*!"; 
    // $SecurityCredential = "Z2tWOKg2yaHdhH9aXcTQ3UeH3ZRANcJtEtLGmJRzJHrlNFv2oxk4XjRNuUy8ujnrTL2+4wDkTnof7IHgttPYDHwSVFImvcyanaZXg9bZkgBq9UhEQAIH68XG0MRL4jZ0UUcSOI9Lm6TU51imUFjxyXmTLzcQoUP/WrWXn4iw5S686UA2gBxoyrFX3E/VF6AhNU5cYvHJHvpPxETlcZb7IUX0XlHOvE35S7yOSOXADObzHYzyeB7kYNSuidDD3YojKV9zm4Ysu9BCErCcHz+drfgkmNTAC7hBMOQ7h6QLJ/rXI6iQBitiniFufv4D+6eAhqTvuTwIChBdicbSrLAS4g==";

    // $payments = [];
    // foreach ($paymentDetails as $payment) {
    //     $phoneNumber = $payment['phoneNumber'];
    //     $amount = $payment['amount'];

    //     if (substr($phoneNumber, 0, 1) == '0') { 
    //         $phoneNumber = '254' . substr($phoneNumber, 1);
    //     }
    //     $phoneNumber = str_replace('+', '', $phoneNumber);
    //     $phoneNumber = str_replace(' ', '', $phoneNumber);

    //     $payments[] = [
    //         "InitiatorName" => "testapi",
    //         "SecurityCredential"=> $SecurityCredential,
    //         "CommandID"=>"SalaryPayment",
    //         "Amount"=> $amount,
    //         "PartyA"=> env('MPESA_SHORTCODE'), 
    //         "PartyB"=> $phoneNumber,
    //         "Remarks"=> "Salary payment",
    //         "QueueTimeOutURL"=> env('MPESA_RESULTS_URL') . '/api/b2c/timeout',
    //         "ResultURL"=> env('MPESA_TIMEOUT_URL') . '/api/result',
    //         "Occassion"=>"Salary payment"
    //     ];
    // }

    // $data = [
    //     'Commands' => 'SalaryPayment',
    //     'InitiatorName' => 'testapi',
    //     'SecurityCredential' => $SecurityCredential,
    //     'SenderIdentifierType' => '4',
    //     'Recipients' => $payments,
    //     'QueueTimeOutURL' => env('MPESA_RESULTS_URL') . '/api/b2c/timeout',
    //     'ResultURL' => env('MPESA_TIMEOUT_URL') . '/api/result'
    // ];

    // $data_string = json_encode($data);
    // $curl = curl_init();
    // curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->getAccessToken())); //setting custom header
    // curl_setopt($curl, CURLOPT_HEADER, false);
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($curl, CURLOPT_POST, true);
    // curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    // $curl_response = curl_exec($curl);
    // $response = json_decode($curl_response);
    // return $response;

}


 //function to generate access token
 public function getAccessToken(){

       $consumer_key = "oEUwoYnqrguLhYItjsRGbyuuLAIBMbF3";
       $consumer_secret = "c5B7cbCdPipljjAq";
       $credentials = base64_encode($consumer_key.':'.$consumer_secret);
       $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
       curl_setopt($curl, CURLOPT_HEADER, false);
       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       $curl_response = curl_exec($curl);
       $response = json_decode($curl_response);
       $access_token = $response->access_token;
       return $access_token;

 }  

 public function checkPaymentStatus (Request $request){
    $request->validate([
        'transactionReference' => 'required|string',
    ]);

    $url = "https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query";
    $pass = "Safaricom999!*!";  
    $SecurityCredential = "Z2tWOKg2yaHdhH9aXcTQ3UeH3ZRANcJtEtLGmJRzJHrlNFv2oxk4XjRNuUy8ujnrTL2+4wDkTnof7IHgttPYDHwSVFImvcyanaZXg9bZkgBq9UhEQAIH68XG0MRL4jZ0UUcSOI9Lm6TU51imUFjxyXmTLzcQoUP/WrWXn4iw5S686UA2gBxoyrFX3E/VF6AhNU5cYvHJHvpPxETlcZb7IUX0XlHOvE35S7yOSOXADObzHYzyeB7kYNSuidDD3YojKV9zm4Ysu9BCErCcHz+drfgkmNTAC7hBMOQ7h6QLJ/rXI6iQBitiniFufv4D+6eAhqTvuTwIChBdicbSrLAS4g==";

    $Initiator = "";
    $transactionID = "";
    $BusinessShortCode ="";
    $phone = "";
    $OriginatorConversationID = "";

    $data = array(
        "Initiator" => $Initiator,
        "SecurityCredential" => $SecurityCredential,
        "Command ID" => "TransactionStatusQuery",
        "Transaction ID" => $transactionID,
        "OriginatorConversationID" => $OriginatorConversationID,
        "PartyA" => $BusinessShortCode,
        "IdentifierType"=> "4",
        "ResultURL" => "http://myservice:8080/transactionstatus/result",
        "QueueTimeOutURL" =>"http://myservice:8080/timeout",
        "Remarks" =>"OK",
        "Occasion" =>"OK",
    );
    
    $data_string = json_encode($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->getAccessToken())); //setting custom header
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    $curl_response = curl_exec($curl);
    $response = json_decode($curl_response);
    return $response;



 }

}
