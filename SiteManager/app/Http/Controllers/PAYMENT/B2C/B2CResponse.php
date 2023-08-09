<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SiteManagerWallet;


class B2CResponse extends Controller
{
    public function b2CResponse(){
        try{
            $content = file_get_contents('php://input');
            $mpesaResponse = json_decode($content, true);
            Log::info($mpesaResponse);

            $resultCode = $mpesaResponse['Result']['ResultCode'];
            $resultDesc = $mpesaResponse['Result']['ResultDesc'];
            $originatorConversationID = $mpesaResponse['Result']['OriginatorConversationID'];
            $conversationID = $mpesaResponse['Result']['ConversationID'];
            $transactionID = $mpesaResponse['Result']['TransactionID'];
            $resultParameters = $mpesaResponse['Result']['ResultParameters']['ResultParameter'];
            $referenceData = $mpesaResponse['Result']['ReferenceData']['ReferenceItem'];
            $transactionAmount = $resultParameters[0]['Value'];
            
            foreach ($resultParameters as $resultParameter) {
                $key = $resultParameter['Key'];
                $value = $resultParameter['Value'];
            
                if ($key == 'ReceiverPartyPublicName') {
                    $phoneNumber = strtok($value, '-');
                    $name = strtok('');
            
                    $receiverName = $name;
                    $receiverPhoneNumber = $phoneNumber;
                }
                if ($key == 'TransactionCompletedDateTime') {
                    $transactionCompletedDateTime = date('Y-m-d H:i:s', strtotime($value));
                    
                }
            }
            
            if($resultCode == 0){
                DB::table('paymentTransactions')->insert([
                    'resultCode' => $resultCode,
                    'resultDesc' => $resultDesc,
                    'originatorConversationId' => $originatorConversationID,
                    'conversationId' => $conversationID,
                    'transactionId' => $transactionID,
                    'transactionAmount' => $resultParameters[0]['Value'],
                    'transactionReceipt' => $resultParameters[1]['Value'],
                    'receiverName' => $receiverName,
                    'receiverPhoneNumber' => $receiverPhoneNumber,
                    'transactionCompletedDateTime' =>  $transactionCompletedDateTime,
                    'utilityAccountAvailableFunds' => $resultParameters[4]['Value'],
                    'workingAccountAvailableFunds' => $resultParameters[5]['Value'],
                    'recipientRegistered' => $resultParameters[6]['Value'],
                    'chargesPaidAvailableFunds' => $resultParameters[7]['Value'],
                ]);

                $phoneNumber = '0'.substr($receiverPhoneNumber, 3); 

                $wallet = SiteManagerWallet::where('phoneNumber', $phoneNumber)->first();
                $wallet->heldBalance -= $transactionAmount;
                $wallet->balance -= $transactionAmount;
                $wallet->save();

                


            }
        }
        catch (Exception $exception){
            Log::error($exception);
        }
    }
}
