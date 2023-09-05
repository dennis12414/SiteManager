<?php

namespace App\Http\Controllers\PAYMENT\C2B;
use App\Http\Controllers\C2BResponse;
use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use App\Models\Transactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class C2BController extends Controller
{
      public function initiatePayment(Request $request){
        try{
            $this->validatePaymentRequest($request);
            $siteManager = $this->findSiteManager($request->phoneNumber);

            $uniqueId = Str::uuid()->toString();
            $result = $this->STKPush($request->msisdn, $request->amount,$uniqueId);

            if(isset($result->success)){
                if($result->success == true){
                    $this->saveDetails($result, $siteManager, $request->amount);
                    return response([
                        'message' => "A push notification is on its way to confirm adding $request->amount KES to your wallet. Provide Your MPESA PIN To Complete The Payment",
                        'partnerReferenceID' => $result->data->data->partnerReferenceID,
                        "success" => true,
                    ], 200);

                }else{
                    return response([
                        'message' => 'Error occured',
                        'error' => $result,
                    ], 400);    
                }

            }

            return response([
                'message' => 'Error occured',
                'error' => $result,
            ], 400);


        }catch(\Exception $e){
            return response([
                'message' => 'Error occured',
                'error' => $e->getMessage()
            ], 400);

        }
      }


      private function validatePaymentRequest(Request $request)
      {
            $request->validate([
                'phoneNumber' => 'required|numeric',
                'msisdn' => 'required|numeric',
                'amount' => 'required|numeric'
            ]);

            //TODO
            if($request->amount < 1 || $request->amount >= 499999){
                abort(400, 'Amount above or below limit');
            }
      }
  
      private function findSiteManager($phoneNumber)
      {
          $siteManager = SiteManager::where('phoneNumber',$phoneNumber)
                        ->where('phoneVerified', true)
                        ->first();
          
  
          if (!$siteManager) {
            abort(404, 'Site Manager does not exist');
          }
          $this->findWallet($siteManager);

          return $siteManager;
      }

      private function findWallet($siteManager)
      {
          $wallet = SiteManagerWallet::where('phoneNumber',$siteManager->phoneNumber)->first();
          if (!$wallet) {
            $wallet = SiteManagerWallet::create([
                'siteManagerId' => $siteManager->siteManagerId,
                'phoneNumber' => $siteManager->phoneNumber,
                'balance' => 0,
                'availableBalance' => 0,
                'heldBalance' =>0,
            ]);
          }
          return $wallet;
      }

      private function saveDetails($result, $siteManager, $amount){
        $wallet = $this->findWallet ($siteManager);
        $transaction = Transactions::create([
            'payType' => 'load',
            'partnerReferenceID' =>$result->data->data->partnerReferenceID,
            'transactionID' => $result->data->data->transactionID,
            'message' =>  $result->message,
            'statusCode' => $result->data->data->statusCode,
            'partnerTransactionID' => null,
            'payerTransactionID' => null,
            'receiptNumber' => null,
            'siteManagerId' => $wallet->siteManagerId,
            'phoneNumber' => $wallet->phoneNumber,
            'transactionAmount' => $amount,
            'transactionStatus' => 'Pending',
        ]);


      }

      private function STKPush(string $phoneNumber, int $amount,$uniqueId){
        $paymentDetails = [
            "paymentCode" => config('settings.paymentCode'),//payment code provided by payment express
            "paymentOption"=>  config('settings.paymentOption'),//payment Method to be used
            "serviceCode"=>  config('settings.C2BserviceCode'),//Payment Express's service to be used
            "msisdn"=> $phoneNumber,//mobile wallet number to be charged in order to load site manager's account
            "accountNumber"=>  config('settings.accountNumber'),//equivalent to a sitemanager unique identifier
            "partnerCallbackUrl"=> config('settings.partnerCallbackUrl') . '/api/confirmation',//url to be called after payment is made
            "amount"=>  $amount,//amount to be charged
            "partnerReferenceID"=>  $uniqueId,//third party's unique ID
            "narration"=> config('settings.narration'),//reason for the payment 

        ];

        $url =  config('settings.c2bUrl');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->getToken()
        ));
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($paymentDetails));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        

        try{
            $response = curl_exec($curl);
            Log::info($response);
            $response = json_decode($response);
           

            if (curl_errno($curl)) {
                throw new \Exception(curl_error($curl));
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return "Error: " . $e->getMessage();
        }finally{
            curl_close($curl);
        }
       
        return $response;

      }

      private function getToken()
      {
          $token = Cache::get('payment_token');
  
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
                  Cache::put('payment_token', $token, 50);
              } else {
                  throw new \Exception('Failed to get payment token');
              }
          }
  
          return $token;
      }

      public function getPaymentStatus(string $partnerReferenceID)
      {
        $paymentDetails = Transactions::where('partnerReferenceID',$partnerReferenceID)->first();
        if(!$paymentDetails){
            return response(['message'=>'Payment was not initiated (partnerReferenceID not found)'],400);
        }
        $wallet =   SiteManagerWallet::where('siteManagerId',$paymentDetails->siteManagerId)->first();

        return response([
            'message'=> $paymentDetails->message,
            'paymentStatus'=>$paymentDetails->transactionStatus,
            'walletBalance'=>$wallet->balance,
            'transactionAmount' => $paymentDetails->transactionAmount,
            'transactionID' => $paymentDetails->transactionID,
            'receiptNumber' => $paymentDetails->receiptNumber,
            'statusCode' => $paymentDetails->statusCode,
        ],200);
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