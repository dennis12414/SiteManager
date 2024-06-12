<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use App\Models\SiteManagerWallet;
use App\Models\SiteManager;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;


class B2CCntroller extends Controller
{
    public function initiatePayment(Request $request)
    {
        try {
            //$this->validatePaymentRequest($request);
            $data = request()->json()->all();
            $siteManager = $this->findSiteManager($data["siteManagerId"]);
            $project = $this->findProject($data["projectId"]);
            $wallet = $this->findSiteManagerWallet($data["siteManagerId"]);

            // Iterate through each payment detail
            foreach ($data["payments"] as $payment) {
                $worker = $this->findWorker($payment['workerId']);
                $clockIns = $this->findClockIn($payment['clockId'], $payment['workerId']);
                //$totalPay = $worker->payRate * count($clockIns);

                // Check if sufficient funds are available
                if ($wallet->availableBalance < $worker->payRate) {
                    return response([
                        'message' => "Insufficient funds in your wallet",
                    ], 404);
                }

                // Call bulkPayment function for each payment detail
                $workerDetails[] = ['name'=>$worker->name, 'phoneNumber' => $worker->phoneNumber, 'amount' => $worker->payRate ];
                $uniqueId = Str::uuid()->toString();

                $result = $this->bulkPayment($workerDetails, $uniqueId);

                if(isset($result->success)){
                    $success = $result->success;

                    if ( $success === true) {
                        // Payment succeeded
                        $payerTransactionID = $result->data->payerTransactionID;
                        $transactionID = $result->data->transactionID;
                        $message = $result->data->message;
                        $statusCode = $result->data->statusCode;


                        Transactions::create([
                            'payType' => 'pay',
                            'statusCode' => $statusCode,
                            'payerTransactionID' => $payerTransactionID,
                            'transactionID' => $transactionID,
                            'message' => $message,
                            'workerId' => $payment['workerId'],
                            'projectId' => $payment['projectId'],
                            'siteManagerId' => $siteManager->siteManagerId,
                            'workDate' => $clockIns->clockInTime,
                            'payRate' => $worker->payRate,
                            'transactionAmount' => $worker->payRate,
                            'transactionStatus' => 'Pending',
                        ]);

                        // Update payment status as processing
                        $clockIns->paymentStatus = 'Pending';
                        $clockIns->save();


                        // Update held balance and available balance
                        $wallet->availableBalance -= $worker->payRate;
                        $wallet->heldBalance += $worker->payRate;
                        $wallet->save();
                    } else {
                        // Payment failed
                        $message = $result->message;

                        Transactions::create([
                            'payType' => 'pay',
                            'statusCode' => '400',
                            'payerTransactionID' => $uniqueId,
                            'message' => $message,
                            'workerId' => $payment['workerId'],
                            'projectId' => $payment['projectId'],
                            'siteManagerId' => $siteManager->siteManagerId,
                            'workDate' => $clockIns->clockInTime,
                            'payRate' => $worker->payRate,
                            'transactionAmount' => $worker->payRate,
                            'transactionStatus' => 'Failed',

                        ]);


//                        return response([
//                            'message' => $message,
//                            'result' => $result,
//                            'success' => $result->success,
//                        ], 400);
                    }
                }
            }

            return response([
                'message' => 'Payments initiated successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response([
                'message' => $e->getMessage(),
            ], 401);
        }

    }


    public function initiatePayment2(Request $request)
    {
        try {

            $this->validatePaymentRequest($request);
            $siteManager = $this->findSiteManager($request->siteManagerId);
            $project = $this->findProject($request->projectId);
            $worker = $this->findWorker($request->workerId);
            $clockIns = $this->findClockIn($request);
            $wallet = $this->findSiteManagerWallet($request->siteManagerId);


            //}elseif($clockIn->paymentStatus === 'Pending'){
                // return response([
                 //    'message' => 'Payment already initiated',
               // ], 404);
            // }



            $wallet = $this->findSiteManagerWallet($request->siteManagerId);

            $totalPay = $worker->payRate * count($clockIns);

            if ($wallet->availableBalance < $totalPay ) {
                return response([
                    'message' => 'Insufficient funds in your wallet',
                ], 404);
            }



            $workerDetails[] = ['name'=>$worker->name, 'phoneNumber' => $worker->phoneNumber, 'amount' => $totalPay ];
            $uniqueId = Str::uuid()->toString();

            //initiate payment
            $result = $this->bulkPayment($workerDetails, $uniqueId);

            //TODO: use status code
            if(isset($result->success)){
                $success = $result->success;

                if($success === true ){
                    $payerTransactionID = $result->data->payerTransactionID;
                    $transactionID = $result->data->transactionID;
                    $message = $result->data->message;
                    $statusCode = $result->data->statusCode;

                foreach($clockIns as $clockIn){
                    Transactions::create([
                        'payType' => 'pay',
                        'statusCode' => $statusCode,
                        'payerTransactionID' => $payerTransactionID,
                        'transactionID' => $transactionID,
                        'message' => $message,
                        'workerId' => $request->workerId,
                        'projectId' => $request->projectId,
                        'siteManagerId' => $request->siteManagerId,
                        'workDate' => $clockIn->clockInTime,
                        'payRate' => $worker->payRate,
                        'transactionAmount' => $worker->payRate,
                        'transactionStatus' => 'Pending',
                    ]);

                    //update payment status as processing
                    $clockIn->paymentStatus = 'Pending';
                    $clockIn->save();

                }


                    //update held balance and available balance
                    $wallet->availableBalance -= $totalPay;
                    $wallet->heldBalance += $totalPay;
                    $wallet->save();




                    return response([
                        'message' => $message,
                        'clockins' => $clockIns,
                        'daysworked' => count($clockIns),
                        'totalPay' => $totalPay,
                        'payerTransactionID'=> $payerTransactionID,
                    ], 200);

                }else{
                    $message = $result->message;
                    foreach($clockIns as $clockIn){
                    Transactions::create([
                        'payType' => 'pay',
                        'statusCode' => '400',
                        'payerTransactionID' => $uniqueId,
                        'message' => $message,
                        'workerId' => $request->workerId,
                        'projectId' => $request->projectId,
                        'siteManagerId' => $request->siteManagerId,
                        'workDate' => $clockIn->clockInTime,
                        'payRate' => $worker->payRate,
                        'transactionAmount' => $worker->payRate,
                        'transactionStatus' => 'Failed',
                    ]);
                 }

                    return response([
                        'message' => $message,
                        'payerTransactionID'=> $uniqueId,
                        'result'=> $result,
                        'success' => $success,
                    ], 400);
                }
            }else{
                return response([
                    'message' => 'Payment request not sent',
                    'payerTransactionID'=> $uniqueId,
                ], 400);
            }


        //TODO: waitForPaymentResponse not needed

        // rest of the code here
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }

    }

    /**
     * bulk payment TMS
     */

    public function bulkPayment($workerDetails, $uniqueId){

        foreach ($workerDetails as $payment) {
            $phoneNumber = $payment['phoneNumber'];
            $amount = $payment['amount'];
            $name = $payment['name'];
        }

        $paymentDetails = [
            'customerName' => $name,
            'msisdn' => $phoneNumber,
            'accountNumber' => $phoneNumber,
            'amount' => $amount,
            'payerNarration' => config('settings.payerNarration'),
            'partnerTransactionID' => $uniqueId,
            'paymentType' => config('settings.paymentType'),
            'serviceCode' => config('settings.serviceCode'),
            'currencyCode' => config('settings.currencyCode'),
            'callbackUrl' =>  config('settings.callbackUrl') . '/api/callback',
        ];

        $url = config('settings.b2cUrl');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);// set url to post to
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->getToken()
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);// return the transfer as a string
        curl_setopt($curl, CURLOPT_POST, true);// set post data to true
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($paymentDetails));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);



        try{
            $response = curl_exec($curl);
            Log::info($response);
            $response = json_decode($response);

            if (curl_errno($curl)) {
                throw new \Exception(curl_error($curl));
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
            //TODO: dont return it to front end
            return "Error: " . $e->getMessage();
        }finally{

            curl_close($curl);
        }

        return $response;

    }


    private function validatePaymentRequest(Request $request)
    {

        $request->validate([
            'siteManagerId' => 'required|numeric',
            'projectId' => 'required|numeric',
            'payments' => 'required|array',
            'payments.*.workerId' => 'required|numeric',
            'payments.*.projectId' => 'required|numeric',
            'payments.*.clockId' => 'required|numeric',
        ]);

    }

    private function findSiteManager($id)
    {
        $siteManager = SiteManager::where('siteManagerId',$id)
        ->where('phoneVerified', true)
        ->first();

        if (!$siteManager) {
          abort(400, 'Site Manager does not exist');
        }

        return $siteManager;
    }

    private function findProject($id)
    {
        $project = Project::where('projectId',$id)->first();

        if (!$project) {
            abort(400, 'Project does not exist');
        }

        return $project;
    }

    private function findWorker($id)
    {
        $worker = Worker::where('workerId',$id)->first();

        if (!$worker) {
            abort(400, 'Worker does not exist');
        }

        return $worker;
    }

    private function findClockIn(int $clockId, int $workerId){

        $query = ClockIns::where('clockId', $clockId)
                    ->where('workerId', $workerId)
                    ->where('paymentStatus', '!=', 'paid')
                    ->first();

        if (!$query) {
            abort(400, "clocked in with Id:: .$clockId. not found! or already paid");
        }

        return $query;
    }

    private function findSiteManagerWallet($id)
    {
        $wallet = SiteManagerWallet::where('siteManagerId', $id)->first();

        if (!$wallet) {
           abort(400, 'Wallet does not exist');
        }

        return $wallet;
    }


    private function getToken()
    {
        $token = Cache::get('payment_token_2');

        if (!$token) {
            $url = config('settings.authUrl');
            $username = config('settings.username');
            $password = config('settings.password');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'username' => $username,
                'password' => $password,
            ]);

            if ($response->ok()) {
                $result = $response->json();
                $token = $result['data']['token'];
                Cache::put('payment_token_2', $token, 50);
            } else {
                throw new \Exception('Failed to get payment token');
            }
        }

        return $token;
    }

    public function getPaymentStatus(string $payerTransactionID){

        $paymentDetails = Transactions::where('payerTransactionID',$payerTransactionID)->first();
        if(!$paymentDetails){
            return response(['message'=>'Payment was not initiated (partnerReferenceID not found)'],400);
        }

        return response([
            'transactionStatus' => $paymentDetails->transactionStatus,
            'message'=> $paymentDetails->message,
            'transactionAmount' => $paymentDetails->transactionAmount,
            'payerTransactionID' => $paymentDetails->payerTransactionID,
            'transactionID' => $paymentDetails->transactionID,
            'receiptNumber' => $paymentDetails->receiptNumber,
            'statusCode' => $paymentDetails->statusCode,
        ],200);
    }





        /**
     * bulk payment daraja
     */
    // public function bulkPayment($paymentDetails, $siteManagerId, $workerId, $projectId, $date)
    // {

    //     $url = "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";
    //     $pass = "Safaricom999!*!";

    //     $SecurityCredential = "FCcKX916X13Ti0xu56p4C+xrjdekT4uNqY5ijpZG6AHmBuQTMEya3p7vUACZ2+vVS68VcuwLaIrK57XrR6ETdCy2hq+wR4xtenuVor07/pIGs5JGF8EDaHBWxcGae4Z/J/fEvWA1DAcyb17e6rCjBSM8VhCPd2PMkqot2lFUtYqp+n91RvNqhUmgPyZ4ghxOlqCosh4vmf1iPL/wMxqu3tar4jSrEApM0EP74jzVw09jwmOnisels0AfCf4b4op7DBsk7OLCeyNM8S8Ufbps/JtCDzUZM6GvwXK1dyhhFw3tYSKGN4F5MANAD/Pvya9MGaMTCXY+e+8vwiPLdjE7Aw==";
    //     //openssl_public_encrypt($pass, $encrypted, $publickey, OPENSSL_PKCS1_PADDING);
    //     //$SecurityCredential = base64_encode($encrypted);
    //     foreach ($paymentDetails as $payment) {
    //         $phoneNumber = $payment['phoneNumber'];
    //         $amount = $payment['amount'];
    //     }
    //     if(substr($phoneNumber, 0, 1) == '0'){
    //         $phoneNumber = '254' . substr($phoneNumber, 1);
    //     }
    //     $phoneNumber = str_replace('+', '', $phoneNumber);
    //     $phoneNumber = str_replace(' ', '', $phoneNumber);


    //     $data =  array(
    //         "InitiatorName" => "testapi",
    //         "SecurityCredential"=> $SecurityCredential,
    //         "CommandID"=>"SalaryPayment",
    //         "Amount"=> $amount,
    //         "PartyA"=> 600983,
    //         "PartyB"=>  "254708374149",
    //         "Remarks"=> "Salary payment",
    //         "QueueTimeOutURL"=> 'https://webhook.site/ff7f5cfc-c681-4a74-8518-f9905ca2abfd',
    //         "ResultURL"=> 'https://a8dc-102-215-76-93.ngrok-free.app/api/result',
    //         "Occassion"=>"Salary payment"
    //     );

    //     $data_string = json_encode($data);
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->getAccessToken())); //setting custom header
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_POST, true);
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    //     curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    //     $curl_response = curl_exec($curl);
    //     $response = json_decode($curl_response);
    //     return $response;

    // }



    // public function initiatePayment(Request $request){
    //     $request->validate([
    //         'siteManagerId' => 'required|numeric',
    //         'projectId' => 'required|numeric',
    //         'workerId' => 'required|numeric',
    //         'date' => 'required|date',
    //     ]);

    //     $siteManager = SiteManager::find($request->siteManagerId);
    //     if(!$siteManager){return response([ 'message' => 'Site Manager does not exist', ], 404);}

    //     $project = Project::find($request->projectId);
    //     if(!$project){ return response([ 'message' => 'Project does not exist', ], 404);}

    //     $worker = Worker::find($request->workerId);
    //     if(!$worker){return response(['message' => 'Worker does not exist',], 404);}

    //     $clockIn = ClockIns::where('projectId', $request->projectId)
    //                         ->where('workerId', $request->workerId)
    //                         ->where('clockInTime', $request->date)
    //                         ->first();
    //     if(!$clockIn){
    //         return response([
    //             'message' => 'Worker did not clock in for  day',
    //         ], 404);
    //     }

    //     //check worker pay rate
    //     $worker->payRate = $worker->payRate;
    //     $phoneNumber = $worker->phoneNumber;


    //     //check if worker has been paid
    //     $status = $clockIn->paymentStatus;
    //     if($status == 'paid' ){
    //         return response([
    //             'message' => 'Worker has already been paid',
    //         ], 404);

    //     }

    //     //check if site manager has enough money in their wallet
    //     $wallet = SiteManagerWallet::where('siteManagerId', $request->siteManagerId)->first();
    //     if(!$wallet){ return response([ 'message' => 'Site Manager does not have a wallet', ], 404);}

    //     $balance = $wallet->balance;
    //     if($balance < $worker->payRate){
    //         return response([
    //             'message' => 'Insufficient funds',
    //             'funds' => $balance,
    //         ], 404);
    //     }

    //     $wallet->heldBalance += $worker->payRate;
    //     $wallet->balance -= $worker->payRate;
    //     $wallet->save();

    //     $clockIn->amountPaid = $worker->payRate;
    //     $clockIn->paymentStatus = 'processing';
    //     $clockIn->save();

    //     $paymentDetails = [
    //         ['phoneNumber' => $phoneNumber, 'amount' => $worker->payRate]
    //     ];

    //     //inititiate payment
    //     $result = $this->bulkPayment($paymentDetails, $request->siteManagerId, $request->projectId, $request->workerId, $request->date);

    //     $retryDelay = [0, 5, 10, 15, 20, 25, 30];

    //     foreach($retryDelay as $delay){
    //         sleep($delay);
    //         $clockIn = ClockIns::where('projectId', $request->projectId)
    //                         ->where('workerId', $request->workerId)
    //                         ->where('clockInTime', $request->date)
    //                         ->first();

    //         $status = $clockIn->paymentStatus;
    //         if($status === 'success'){
    //             // $wallet->heldBalance -= $worker->payRate;
    //             // $wallet->save();
    //             return response([
    //                 'message' => 'Payment successful',
    //             ], 200);
    //         }elseif($status === 'failed'){
    //             // $wallet->balance += $worker->payRate;
    //             // $wallet->heldBalance -= $worker->payRate;
    //             // $wallet->save();
    //             return response([
    //                 'message' => 'Payment failed',
    //             ], 404);
    //         }else{
    //             continue; //retry
    //         }
    //     }

    //     return response([
    //         'message' => 'Payment timed out',
    //     ], 404);

    // }




 //function to generate access token
//  public function getAccessToken(){

//        $consumer_key = "oEUwoYnqrguLhYItjsRGbyuuLAIBMbF3";
//        $consumer_secret = "c5B7cbCdPipljjAq";
//        $credentials = base64_encode($consumer_key.':'.$consumer_secret);
//        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
//        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        $curl_response = curl_exec($curl);
//        $response = json_decode($curl_response);
//        $access_token = $response->access_token;
//        return $access_token;

//  }

//  public function checkPaymentStatus (Request $request){
//     $request->validate([
//         'transactionReference' => 'required|string',
//     ]);

//     $url = "https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query";
//     $pass = "Safaricom999!*!";
//     $SecurityCredential = "Z2tWOKg2yaHdhH9aXcTQ3UeH3ZRANcJtEtLGmJRzJHrlNFv2oxk4XjRNuUy8ujnrTL2+4wDkTnof7IHgttPYDHwSVFImvcyanaZXg9bZkgBq9UhEQAIH68XG0MRL4jZ0UUcSOI9Lm6TU51imUFjxyXmTLzcQoUP/WrWXn4iw5S686UA2gBxoyrFX3E/VF6AhNU5cYvHJHvpPxETlcZb7IUX0XlHOvE35S7yOSOXADObzHYzyeB7kYNSuidDD3YojKV9zm4Ysu9BCErCcHz+drfgkmNTAC7hBMOQ7h6QLJ/rXI6iQBitiniFufv4D+6eAhqTvuTwIChBdicbSrLAS4g==";

//     $Initiator = "";
//     $transactionID = "";
//     $BusinessShortCode ="";
//     $phone = "";
//     $OriginatorConversationID = "";

//     $data = array(
//         "Initiator" => $Initiator,
//         "SecurityCredential" => $SecurityCredential,
//         "Command ID" => "TransactionStatusQuery",
//         "Transaction ID" => $transactionID,
//         "OriginatorConversationID" => $OriginatorConversationID,
//         "PartyA" => $BusinessShortCode,
//         "IdentifierType"=> "4",
//         "ResultURL" => "http://myservice:8080/transactionstatus/result",
//         "QueueTimeOutURL" =>"http://myservice:8080/timeout",
//         "Remarks" =>"OK",
//         "Occasion" =>"OK",
//     );

//     $data_string = json_encode($data);
//     $curl = curl_init();
//     curl_setopt($curl, CURLOPT_URL, $url);
//     curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$request->getAccessToken())); //setting custom header
//     curl_setopt($curl, CURLOPT_HEADER, false);
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($curl, CURLOPT_POST, true);
//     curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
//     curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

//     $curl_response = curl_exec($curl);
//     $response = json_decode($curl_response);
//     return $response;
//  }

//  public function receiveResponse($response){


//  }



}
