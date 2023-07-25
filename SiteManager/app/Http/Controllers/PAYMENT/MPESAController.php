<?php

namespace App\Http\Controllers\PAYMENT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MPESAController extends Controller
{
   //function to business to customer using daraja api
   public function b2cRequest(){
      $url = "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";

      $data =  array(  
            "InitiatorName" => "testapi",
            "SecurityCredential"=> "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919",
            "CommandID"=>"SalaryPayment",
            "Amount"=>1,
            "PartyA"=>env('MPESA_SHORTCODE'),
            "PartyB"=>"254112727544",
            "Remarks"=> "Salary payment",
            "QueueTimeOutURL"=> env('MPESA_RESULTS_URL') . '/api/b2c/timeout',
            "ResultURL"=> env('MPESA_TIMEOUT_URL') . '/api/b2c/result',
            "Occassion"=>""
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
      return $data;
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
   
   public function callback(Request $request){
      Log::info($request->all());
      $result = $request->all();
      return [
            'ResultCode' => 0,
            'ResultDesc' => 'Success',
            'ThirdPartyTransID' => '1234567890'    
      ];
   }
}
