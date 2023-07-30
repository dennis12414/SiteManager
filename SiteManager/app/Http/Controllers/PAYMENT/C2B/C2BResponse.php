<?php

namespace App\Http\Controllers\PAYMENT\C2B;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class C2BResponse extends Controller
{
    public function confirmation()
    {
        try {
            $content = file_get_contents('php://input');
            $mpesaResponse = json_decode($content, true); 
            date_default_timezone_set('Africa/Nairobi');
            $date = date('Y-m-d H:i:s');
            Log::info($date);
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

                //add amount to sitemanager wallet
                $siteManager = SiteManager::where('phoneNumber', $phoneNumber)->first();
                if(!$siteManager){
                    return response([
                        'message' => 'Site manager does not exist',
                    ], 404);
                }

                $wallet = SiteManagerWallet::where('phoneNumber', $phoneNumber)->first();
                if(!$wallet){
                    // $wallet = SiteManagerWallet::create([
                    //     'siteManagerId' => $siteManager->siteManagerId,
                    //     'phoneNumber' => $phoneNumber,
                    //     'balance' => $amount,
                    // ]);
                 
                }else{
                    $wallet->balance += $amount;
                    $wallet->save();
                }
            }
            } catch (Exception $exception) {
                Log::error($exception);
            }
    }

}
