<?php

namespace App\Http\Controllers\PAYMENT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class MPESAController extends Controller
{
   //function to business to customer using daraja api
   public function simulate(Request $request){
      $request->validate([
            'projectId'=> 'required|numeric',
            'startDate' => 'date',
      ]);
   
      //check if project exists
      $project = Project::where('projectId', $request->projectId)->first();
      if(!$project){
          return response([
              'message' => 'Project does not exist',
          ], 404);
      }

      $clockIns = ClockIns::where('projectId', $request->projectId)
                        ->where('date', $request->startDate)
                        ->get();
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
      $responses = [];
      foreach($workers as $worker){
            $workerData[] = [
                  'workerId' => $worker->workerId,
                  'name' => $worker->name,
                  'phoneNumber' => $worker->phoneNumber,
                  'payRate' => $worker->payRate,
            ];
            $response = $this->b2cRequest($worker->phoneNumber, 100);
            $responses[] = $response;
      }

      return response([
            'message' => 'Success',
            'data' => $workerData,
            'responses' => $responses,
      ], 200);
   
}


public function b2cRequest($phoneNumber, $amount){

      $url = "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";
      $pass = "Safaricom999!*!"; 
      
      $SecurityCredential = "Z2tWOKg2yaHdhH9aXcTQ3UeH3ZRANcJtEtLGmJRzJHrlNFv2oxk4XjRNuUy8ujnrTL2+4wDkTnof7IHgttPYDHwSVFImvcyanaZXg9bZkgBq9UhEQAIH68XG0MRL4jZ0UUcSOI9Lm6TU51imUFjxyXmTLzcQoUP/WrWXn4iw5S686UA2gBxoyrFX3E/VF6AhNU5cYvHJHvpPxETlcZb7IUX0XlHOvE35S7yOSOXADObzHYzyeB7kYNSuidDD3YojKV9zm4Ysu9BCErCcHz+drfgkmNTAC7hBMOQ7h6QLJ/rXI6iQBitiniFufv4D+6eAhqTvuTwIChBdicbSrLAS4g==";
      //openssl_public_encrypt($pass, $encrypted, $publickey, OPENSSL_PKCS1_PADDING);
      //$SecurityCredential = base64_encode($encrypted);

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
            "PartyA"=>env('MPESA_SHORTCODE'), 
            "PartyB"=> $phoneNumber,
            "Remarks"=> "Salary payment",
            "QueueTimeOutURL"=> env('MPESA_RESULTS_URL') . '/api/b2c/timeout',
            "ResultURL"=> env('MPESA_TIMEOUT_URL') . '/api/result',
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

   }

   //function to generate access token
   public function getAccessToken(){

         $consumer_key = env('MPESA_CONSUMER_KEY');
         $consumer_secret = env('MPESA_CONSUMER_SECRET');
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
   
}
