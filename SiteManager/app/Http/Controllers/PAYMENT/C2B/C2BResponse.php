<?php

namespace App\Http\Controllers\PAYMENT\C2B;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class C2BResponse extends Controller
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function confirmation()
    {
        try {
            $content = file_get_contents('php://input');
            $mpesaResponse = json_decode($content, true); 
            
            Log::info($mpesaResponse);
            
            $resultCode = $mpesaResponse['Body']['stkCallback']['ResultCode'];
            $resultDesc = $mpesaResponse['Body']['stkCallback']['ResultDesc'];
            $merchantRequestID = $mpesaResponse['Body']['stkCallback']['MerchantRequestID'];
            $checkoutRequestID = $mpesaResponse['Body']['stkCallback']['CheckoutRequestID'];
            $amount = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
            $mpesaReceiptNumber = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
            $transactionDate = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
            $transactionDate = date('Y-m-d H:i:s', strtotime($transactionDate));
            $phoneNumber = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

            if($resultCode == 0){ 
                $mpesa = new MpesaTransaction();
                $mpesa->merchantRequestID = $merchantRequestID;
                $mpesa->checkoutRequestID = $checkoutRequestID;
                $mpesa->amount = $amount;
                $mpesa->mpesaReceiptNumber = $mpesaReceiptNumber;
                $mpesa->transactionDate = $transactionDate;
                $mpesa->phoneNumber = $phoneNumber;
                $mpesa->save();
                Log::info('transaction saved');

                
                $phoneNumber = '0'.substr($phoneNumber, 3); 
                Log::info($phoneNumber);

                //add amount to sitemanager wallet
                $siteManager = SiteManager::where('phoneNumber', $phoneNumber)
                                ->where('phoneVerified', true)
                                ->first();
                if(!$siteManager){
                    //log phone number
                    Log::info($phoneNumber);
                    Log::info('site manager does not exist');
                 
                }

                $wallet = SiteManagerWallet::where('phoneNumber', $phoneNumber)->first();
                if(!$wallet){
                    Log::info('wallet does not exist');
                    $wallet = SiteManagerWallet::create([
                        'siteManagerId' => $siteManager->siteManagerId,
                        'phoneNumber' => $phoneNumber,
                        'balance' => $amount,
                        'availableBalance' => $amount,
                    ]);
                    Log::info('wallet created');
                 
                }else{
                    Log::info('wallet exists');
                    $wallet->balance += $amount;
                    $wallet->save();
                }

                return response([
                    'message' => 'Transaction saved successfully',
                    //'response' => $mpesaResponse,
                    //'balance' => $wallet->balance
                ], 200);
            }
        
            } catch (Exception $exception) {
                Log::error($exception);
            }
        
    }

}
