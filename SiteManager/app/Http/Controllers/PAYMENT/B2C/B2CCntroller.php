<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use App\Models\SiteManagerWallet;
use App\Models\SiteManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;


class B2CCntroller extends Controller
{

    public function initiatePayment(Request $request)
    {
        try {
            
            $siteManager = $this->findSiteManager($request->siteManagerId);
            $project = $this->findProject($request->projectId);
            $worker = $this->findWorker($request->workerId);
            $clockIn = $this->findClockIn($request->projectId, $request->workerId,$request->date);
            $wallet = $this->findSiteManagerWallet($request->siteManagerId);

            if ($clockIn->paymentStatus === 'paid') {
                return response([
                    'message' => 'Duplicate Payment',
                ], 404);

            }elseif($clockIn->paymentStatus === 'pending processing'){
                return response([
                    'message' => 'Payment already initiated',
                ], 404);
            }

            
            
            $wallet = $this->findSiteManagerWallet($request->siteManagerId);

            if ($wallet->availableBalance < $worker->payRate) {
                return response([
                    'message' => 'Insufficient funds',
                ], 404);
            }
            
          

            $workerDetails[] = ['name'=>$worker->name, 'phoneNumber' => $worker->phoneNumber, 'amount' => $worker->payRate];
            $uniqueId = Str::uuid()->toString();
            //initiate payment 
            $result = $this->bulkPayment($workerDetails, $request->siteManagerId, $request->projectId, $request->workerId, $request->date, $uniqueId);

            if(isset($result->ResponseCode) || isset($result->success)){
                $success = $result->success; //TMS
                //$success = $result->ResponseCode; //daraja
                $payerTransactionID = $result->data->payerTransactionID;
                $transactionID = $result->data->transactionID;
                $message = $result->data->message;
                DB::table('paymentTransactions')->insert([
                    'payerTransactionID' => $payerTransactionID,
                    'transactionID' => $transactionID,
                    'message' => $message,
                    'workerId' => $request->workerId,
                    'projectId' => $request->projectId,
                    'siteManagerId' => $request->siteManagerId,
                    'workDate' => $request->date,
                    'payRate' => $worker->payRate,
                ]);
                
            }else{
                return response([
                    'message' => 'Payment request not sent',
                    'unique'=> $uniqueId,
                    'result'=> $result,
            
                ], 400);
            }
            
            if($success === false ){
                return response([
                    'message' => 'Payment Validation Failed',
                    'result'=> $result,
            
                ], 400);

            }else{
                //update held balance and available balance 
                $wallet->availableBalance -= $worker->payRate;
                $wallet->heldBalance += $worker->payRate;
                $wallet->save();

                //update payment status as processing
                $clockIn->paymentStatus = 'pending processing';
                $clockIn->save();
            }

            $paymentResponse = $this->waitForPaymentResponse($request->projectId, $request->workerId, $request->date);

            if ($paymentResponse === 'paid') {
                return response([
                    'message' => 'Payment successfull',
                ], 200);
            } elseif($paymentResponse === 'failed') {
                return response([
                    'message' => 'Payment failed',
                ], 404);

            }else{
                return response([
                    'message' => 'Payment timed out, (no callback received)',
                    'result' => $result,
                ], 404);

            }


    
            // rest of the code here
        }catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 404);
        }

    }

    private function waitForPaymentResponse($projectId, $workerId, $date)
    {
        // Retry delays in seconds
        $retryDelays = [0, 2, 3];

        foreach ($retryDelays as $delay) {
            sleep($delay);

            $clockIn = ClockIns::where('projectId', $projectId)
                                ->where('workerId', $workerId)
                                ->where('clockInTime', $date)
                                ->first();

            if ($clockIn->paymentStatus === 'paid') {
                return 'paid';
            } elseif ($clockIn->paymentStatus === 'failed') {
                return 'failed';
            }
            // Payment still processing
            continue;
        }
        // Timed out
        return false;
    }

    /**
     * bulk payment TMS
     */

    public function bulkPayment($workerDetails,$siteManagerId, $workerId, $projectId, $date,$uniqueId){

        foreach ($workerDetails as $payment) {
            $phoneNumber = $payment['phoneNumber'];
            $amount = $payment['amount'];
            $name = $payment['name'];
        }
        if(substr($phoneNumber, 0, 1) == '0'){ 
              $phoneNumber = '254' . substr($phoneNumber, 1);
        }
        $phoneNumber = str_replace('+', '', $phoneNumber);
        $phoneNumber = str_replace(' ', '', $phoneNumber);

        

        // $uniqueId = Str::uuid()->toString();

        $paymentDetails = [
            'customerName' => $name,
            'msisdn' => $phoneNumber,
            'accountNumber' => $phoneNumber,
            'amount' => 10,
            'payerNarration' => 'Payment Completed Successfully',
            'partnerTransactionID' => $uniqueId,
            'paymentType' => 'BusinessPayment',
            'serviceCode' => 'MPESAB2C',
            'currencyCode' => 'KES',
            'callbackUrl' => 'http://172.105.90.112/site-manager-backend/SiteManager/api/callback',
            
        ];

        $url = "http://172.105.90.112:8080/paymentexpress/v1/payment/create";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->getToken()
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($paymentDetails));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    

    private function validatePaymentRequest(Request $request)
    {
        $request->validate([
            'siteManagerId' => 'required|numeric',
            'projectId' => 'required|numeric',
            'workerId' => 'required|numeric',
            'date' => 'required|date',
        ]);

    }

    private function findSiteManager($id)
    {
        $siteManager = SiteManager::find($id);

        if (!$siteManager) {
          abort(404, 'Site Manager does not exist');
        }

        return $siteManager;
    }

    private function findProject($id)
    {
        $project = Project::find($id);

        if (!$project) {
            abort(404, 'Project does not exist');
        }

        return $project;
    }

    private function findWorker($id)
    {
        $worker = Worker::find($id);

        if (!$worker) {
            abort(404, 'Worker does not exist');
        }

        return $worker;
    }

    private function findClockIn($projectId, $workerId, $date)
    {
        $clockIn = ClockIns::where('projectId', $projectId)
                            ->where('workerId', $workerId)
                            ->where('clockInTime', $date)
                            ->first();

        if (!$clockIn) {
           abort(404, 'Not clocked in!');
        }

        return $clockIn;
    }

    private function findSiteManagerWallet($id)
    {
        $wallet = SiteManagerWallet::where('siteManagerId', $id)->first();

        if (!$wallet) {
           abort(404, 'Wallet does not exist');
        }

        return $wallet;
    }


    private function getToken()
    {
        $token = Cache::get('payment_token');

        if (!$token) {
            $url = "http://172.105.90.112:8080/paymentexpress/v1/client/users/authenticate";
            $username = "ikoaqua-mpesa-user";
            $password = "F5Hm5CNDg0kG";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'username' => $username,
                'password' => $password,
            ]);

            if ($response->ok()) {
                $result = $response->json();
                $token = $result['data']['token'];
                Cache::put('payment_token', $token, 50);
            } else {
                throw new \Exception('Failed to get payment token');
            }
        }

        return $token;
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
