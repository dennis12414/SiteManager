<?php

namespace App\Http\Controllers\PAYMENT\C2B;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use App\Models\Transactions;
use Illuminate\Support\Facades\Log;


class C2BResponse extends Controller
{
    

    public function confirmation()
    {

        try{
            // Get the request content and decode it
            $content = file_get_contents('php://input');
            $mpesaResponse = json_decode($content, true);

            // Log the response
            Log::info($mpesaResponse);

            // Extract necessary data from the response
            $statusCode = $mpesaResponse['statusCode'];
            $message = $mpesaResponse['message'];
            $providerNarration = $mpesaResponse['providerNarration'];
            $partnerTransactionID = $mpesaResponse['partnerTransactionID'];
            $payerTransactionID = $mpesaResponse['payerTransactionID'];
            $receiptNumber = $mpesaResponse['receiptNumber'];
            $transactionID = $mpesaResponse['transactionID'];

            // Check if payment has initiated or already been processed
            $paymentDetails = $this->getPaymentDetails($partnerTransactionID);

            if($statusCode === "00"){
                $transactionStatus = "Success";
                $this->updatePaymentDetails($partnerTransactionID, $statusCode, $message,$receiptNumber, $transactionID, $transactionStatus,$payerTransactionID);
                $this->updateWalletBalance($paymentDetails );
                return response([
                    'valid'=> true,
                    'message' => 'Processed successfully',
                    'statusCode'=> '00',
                ], 200);
        
            }else{
                $transactionStatus = "Failed";
                $this->updatePaymentDetails($partnerTransactionID, $statusCode, $message,$receiptNumber, $transactionID,$transactionStatus,$payerTransactionID);
                return response([
                    'valid'=> false,
                    'message' => 'Request cancelled by user', 
                    'statusCode'=> $statusCode,
                ], 200);
            }

        }catch(\Exception $e){
            Log::info($e->getMessage());
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }


        // try {
        //     $content = file_get_contents('php://input');
        //     $mpesaResponse = json_decode($content, true); 
            
        //     Log::info($mpesaResponse);
            
        //     $resultCode = $mpesaResponse['Body']['stkCallback']['ResultCode'];
        //     $resultDesc = $mpesaResponse['Body']['stkCallback']['ResultDesc'];
        //     $merchantRequestID = $mpesaResponse['Body']['stkCallback']['MerchantRequestID'];
        //     $checkoutRequestID = $mpesaResponse['Body']['stkCallback']['CheckoutRequestID'];
        //     $amount = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
        //     $mpesaReceiptNumber = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
        //     $transactionDate = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
        //     $transactionDate = date('Y-m-d H:i:s', strtotime($transactionDate));
        //     $phoneNumber = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

        //     if($resultCode == 0){ 
        //         $mpesa = new MpesaTransaction();
        //         $mpesa->merchantRequestID = $merchantRequestID;
        //         $mpesa->checkoutRequestID = $checkoutRequestID;
        //         $mpesa->amount = $amount;
        //         $mpesa->mpesaReceiptNumber = $mpesaReceiptNumber;
        //         $mpesa->transactionDate = $transactionDate;
        //         $mpesa->phoneNumber = $phoneNumber;
        //         $mpesa->save();
        //         Log::info('transaction saved');

                
        //         $phoneNumber = '0'.substr($phoneNumber, 3); 
        //         Log::info($phoneNumber);

        //         //add amount to sitemanager wallet
        //         $siteManager = SiteManager::where('phoneNumber', $phoneNumber)
        //                         ->where('phoneVerified', true)
        //                         ->first();
        //         if(!$siteManager){
        //             //log phone number
        //             Log::info($phoneNumber);
        //             Log::info('site manager does not exist');
                 
        //         }

        //         $wallet = SiteManagerWallet::where('phoneNumber', $phoneNumber)->first();
        //         if(!$wallet){
        //             Log::info('wallet does not exist');
        //             $wallet = SiteManagerWallet::create([
        //                 'siteManagerId' => $siteManager->siteManagerId,
        //                 'phoneNumber' => $phoneNumber,
        //                 'balance' => $amount,
        //                 'availableBalance' => $amount,
        //             ]);
        //             Log::info('wallet created');
                 
        //         }else{
        //             Log::info('wallet exists');
        //             $wallet->balance += $amount;
        //             $wallet->save();
        //         }

        //         return response([
        //             'message' => 'Transaction saved successfully',
        //             //'response' => $mpesaResponse,
        //             //'balance' => $wallet->balance
        //         ], 200);
        //     }
        
        //     } catch (\Exception $exception) {
        //         Log::error($exception);
        //     }
        
    }

    private function updateWalletBalance($paymentDetails){
        $wallet = SiteManagerWallet::where('siteManagerId',$paymentDetails->siteManagerId)->first();
        $wallet->balance += $paymentDetails->transactionAmount;
        $wallet->availableBalance += $paymentDetails->transactionAmount;
        $wallet->save();
    }

    private function updatePaymentDetails($partnerTransactionID, $statusCode, $message,$receiptNumber, $transactionID,$transactionStatus,$payerTransactionID)
    {
        $paymentDetails =  $this->getPaymentDetails($partnerTransactionID);
        $paymentDetails->statusCode = $statusCode;
        $paymentDetails->message = $message;
        $paymentDetails->receiptNumber = $receiptNumber;
        $paymentDetails->transactionID = $transactionID;
        $paymentDetails->transactionStatus = $transactionStatus;
        $paymentDetails->payerTransactionID= $payerTransactionID;
        $paymentDetails->save();
    }

    private function getPaymentDetails($partnerReferenceID){
        $paymentDetails = Transactions::where('partnerReferenceID',$partnerReferenceID)->first();
        if(!$paymentDetails){
            abort(400, 'Payment was not initiated (partnerReferenceID not found)');
        }
        if ($paymentDetails->transactionStatus === 'Success') {
            abort(200, 'Payment already processed ');
        }

        return $paymentDetails;
    }

}
