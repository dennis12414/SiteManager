<?php

namespace App\Http\Controllers\PAYMENT\C2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class C2BController extends Controller
{
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

      public function STKPush(){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $access_token = $this->getAccessToken();
            $BusinessShortCode = env('MPESA_C2B_SHORTCODE');
            $Passkey = env('MPESA_PASSKEY');
            date_default_timezone_set('Africa/Nairobi');
            $Timestamp = date('YmdHis');
            $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);
            $PartyA = env('MPESA_SHORTCODE');
            $callBackURL = env('MPESA_CALLBACK_URL') . '/api/confirmation';
            $AccountReference = "Test";
            $TransactionDesc = "Test";
            $Amount = 1;
            $PhoneNumber = 254112727544;

            $data = array(
                  "BusinessShortCode" => $BusinessShortCode,
                  "Password" => $Password,
                  "Timestamp" => $Timestamp,
                  "TransactionType" => "CustomerPayBillOnline",
                  "Amount" => $Amount,
                  "PartyA" => $PhoneNumber,
                  "PartyB" => $BusinessShortCode,
                  "PhoneNumber" => $PhoneNumber,
                  "CallBackURL" => $callBackURL,
                  "AccountReference" => $AccountReference,
                  "TransactionDesc" => $TransactionDesc
            );

            $dataString = json_encode($data);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$access_token)); //setting custom header
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
            $curl_response = curl_exec($curl);
            
            return $curl_response;

      }

}