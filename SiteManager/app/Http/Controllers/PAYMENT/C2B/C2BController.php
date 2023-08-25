<?php

namespace App\Http\Controllers\PAYMENT\C2B;
use App\Http\Controllers\C2BResponse;
use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class C2BController extends Controller
{
      public function initiatePayment(Request $request){
            $request->validate([
                'phoneNumber' => 'required|numeric',
                'amount' => 'required|numeric'
            ]);
    
            $siteManager = SiteManager::where('phoneNumber', $request->phoneNumber)
                            ->where('phoneVerified', true)
                            ->first();

            if(!$siteManager){
                return response([
                    'message' => 'Site manager does not exist',
                ], 404);
            }

            //if amount is greater than 150 000 then return error
            if($request->amount > 150000){
                return response([
                    'message' => 'Amount cannot be greater than 150,000',
                ], 400);
            }

            $phoneNumber = $request->phoneNumber;
            // if(substr($phoneNumber, 0, 1) == '0'){ 
            //       $phoneNumber = '254' . substr($phoneNumber, 1);
            // }
            // $phoneNumber = str_replace('+', '', $phoneNumber);
            // $phoneNumber = str_replace(' ', '', $phoneNumber);

            $response = $this->STKPush($phoneNumber, $request->amount, $siteManager);
            //$res = Http::post(env('MPESA_CALLBACK_URL') . '/api/confirmation');

            return response([
                'message' => 'Debited successfully',
            ], 200);

      }

      public function STKPush(string $phoneNumber, int $amount,$siteManager){
        $wallet = SiteManagerWallet::where('phoneNumber', $phoneNumber)->first();
        if(!$wallet){
            $wallet = SiteManagerWallet::create([
                'siteManagerId' => $siteManager->siteManagerId,
                'phoneNumber' => $phoneNumber,
                'balance' => $amount,
                'availableBalance' => $amount,
            ]);
        }else{
            $wallet->availableBalance  += $amount;
            $wallet->balance += $amount;
            $wallet->save();
        }
      }

      
      
    //   public function STKPush(string $phoneNumber, int $amount){
    //         $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            
    //         $access_token = $this->getAccessToken();
    //         $BusinessShortCode = 174379;
    //         $Passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
    //         date_default_timezone_set('Africa/Nairobi');
    //         $Timestamp = date('YmdHis');
    //         $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);
    //         $PartyA = env('MPESA_SHORTCODE');
    //         $callBackURL = env('APP_URL') . '/api/confirmation';
    //         $AccountReference = "Test";
    //         $TransactionDesc = "Test";
         

    //         $data = array(
    //               "BusinessShortCode" => $BusinessShortCode,
    //               "Password" => $Password,
    //               "Timestamp" => $Timestamp,
    //               "TransactionType" => "CustomerPayBillOnline",
    //               "Amount" => $amount,
    //               "PartyA" => $phoneNumber,
    //               "PartyB" => $BusinessShortCode,
    //               "PhoneNumber" => $phoneNumber,
    //               "CallBackURL" => $callBackURL,
    //               "AccountReference" => $AccountReference,
    //               "TransactionDesc" => $TransactionDesc
    //         );

    //         $dataString = json_encode($data);
    //         $curl = curl_init();
    //         curl_setopt($curl, CURLOPT_URL, $url);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$access_token)); //setting custom header
    //         curl_setopt($curl, CURLOPT_HEADER, false);
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($curl, CURLOPT_POST, true);
    //         curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
    //         $curl_response = curl_exec($curl);
            
    //         return $curl_response;
    //   }

    //   public function getAccessToken(){
    //         $consumer_key = "oEUwoYnqrguLhYItjsRGbyuuLAIBMbF3";
    //         $consumer_secret = "c5B7cbCdPipljjAq";
    //         $credentials = base64_encode($consumer_key.':'.$consumer_secret);
    //         $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    //         $curl = curl_init();
    //         curl_setopt($curl, CURLOPT_URL, $url);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
    //         curl_setopt($curl, CURLOPT_HEADER, false);
    //         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //         $curl_response = curl_exec($curl);
    //         $response = json_decode($curl_response);
    //         $access_token = $response->access_token;
    //         return $access_token;
    //   }

    //   public function hundleCallback(Request $request){
        
    //     $response = C2BResponse::confirmation();

    //   }

}