<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SiteManagerWallet;
use App\Models\ClockIns;



class B2CResponse extends B2CBase
{
    public function b2CResponse(Request $request){
        try{

            $content = file_get_contents('php://input');
            $workerId = $request->query('workerId');
            $projectId = $request->query('projectId');
            $date = $request->query('date');
            $payRate = $request->query('payRate');
            $siteManagerId = $request->query('siteManagerId');
            $mpesaResponse = json_decode($content, true);

            Log::info($mpesaResponse);
            $resultCode = $mpesaResponse['Result']['ResultCode'];
            Log::info($resultCode);
            Log::info($workerId);
            Log::info($projectId);
            Log::info($date);
            Log::info($payRate);
            Log::info($siteManagerId);



            /**
             * Handles TMS API response
             */
            
            $statusCode = $mpesaResponse->statusCode;
            $message = $mpesaResponse->message;
            $providerNarration = $mpesaResponse->providerNarration;
            $partnerTransactionID = $mpesaResponse->partnerTransactionID;
            $payerTransactionID = $mpesaResponse->payerTransactionID;
            $receiptNumber = $mpesaResponse->receiptNumber;
            $transactionID = $mpesaResponse->transactionID;

            $wallet = SiteManagerWallet::where('siteManagerId', $siteManagerId)->first();
            $clockIn = ClockIns::where('projectId', $projectId)
                        ->where('workerId', $workerId)
                        ->where('clockInTime', $date)
                        ->first();

            if($statusCode = 00){
                $wallet->balance -= $payRate;
                $wallet->save();

                $clockIn->paymentStatus = 'paid';
                $clockIn->save();
            }else{
                $clockIn->paymentStatus = 'failed';
                $clockIn->save();

                $wallet->availableBalance += $payRate;
                $wallet->heldBalance -= $payRate;
                $wallet->save();
            }

 

            

            
            /**
             * Handles daraja API response
             */


            // $resultCode = $mpesaResponse['Result']['ResultCode'];
            // $resultDesc = $mpesaResponse['Result']['ResultDesc'];
            // $originatorConversationID = $mpesaResponse['Result']['OriginatorConversationID'];
            // $conversationID = $mpesaResponse['Result']['ConversationID'];
            // $transactionID = $mpesaResponse['Result']['TransactionID'];

            // $referenceData = $mpesaResponse['Result']['ReferenceData']['ReferenceItem'];


            // $clockIn = ClockIns::where('projectId', $projectId)
            //             ->where('workerId', $workerId)
            //             ->where('clockInTime', $date)
            //             ->first();
            // if(!$clockIn){
            //     Log::info('Clock in not found');
                
            // }
            // Log::info($resultCode);
            // if($resultCode == 0){
                // DB::table('paymentTransactions')->insert([
                //     'resultCode' => $resultCode,
                //     'resultDesc' => $resultDesc,
                //     'originatorConversationId' => $originatorConversationID,
                //     'conversationId' => $conversationID,
                //     'transactionId' => $transactionID,
                //     'transactionAmount' => $resultParameters[0]['Value'],
                //     'transactionReceipt' => $resultParameters[1]['Value'],
                //     'receiverName' => $receiverName,
                //     'receiverPhoneNumber' => $receiverPhoneNumber,
                //     'transactionCompletedDateTime' =>  $transactionCompletedDateTime,
                //     'utilityAccountAvailableFunds' => $resultParameters[4]['Value'],
                //     'workingAccountAvailableFunds' => $resultParameters[5]['Value'],
                //     'recipientRegistered' => $resultParameters[6]['Value'],
                //     'chargesPaidAvailableFunds' => $resultParameters[7]['Value'],
                // ]);

                //$clockIn->amountPaid = $transactionAmount;
        //         $clockIn->paymentStatus = 'paid';
        //         $clockIn->save();
                
        //     }else{

        //         $clockIn->paymentStatus = 'failed';
        //         $clockIn->save();
        //     }
        }

        catch (\Exception $exception){
            Log::error($exception);
        }
    } 
}
