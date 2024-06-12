<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SiteManagerWallet;
use App\Models\Transactions;
use App\Models\ClockIns;



class B2CResponse extends Controller
{
    public function b2CResponse()
    {
        try {
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

            // Check if payment has already been processed
            $paymentDetails = $this->getPaymentDetails($payerTransactionID);
            $clockIns = $this->getClockInDetails($paymentDetails);
            $payRate = $paymentDetails->payRate;
            $totalPay = $payRate;
            $wallet = $this->getWallet($paymentDetails->siteManagerId);


            // Update wallet and clock in details based on payment status
            if ($statusCode === "00") {
                $transactionStatus = "Success";
                $this->updateWalletAndClockInSuccess($wallet, $clockIns,$payRate, $totalPay);
                $this->updatePaymentDetails($paymentDetails, $statusCode, $message,  $receiptNumber, $transactionID, $transactionStatus);

            }else{
                $transactionStatus = "Failed";
                $this->updateWalletAndClockInFail($wallet, $clockIns, $totalPay);

            }

            // Return success response
            return response([
                'payRate' => $payRate,
                'totalPay' => $totalPay,
                'clockIns' => $clockIns,
                'message' => 'Payment processed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response([
                'message' => $e->getMessage(),

            ], 400);
        }
    }

    private function updateWalletAndClockInSuccess($wallet, $clockIn, $payRate, $totalPay)
    {
        // Update the wallet
        $wallet->heldBalance -= $totalPay;
        $wallet->balance -= $totalPay;
        $wallet->save();

        // Update the clock in details
        //foreach($clockIn as $clockIn){
            $clockIn->paymentStatus = 'paid';
            $clockIn->amountPaid = $payRate;
            $clockIn->save();
        //}
    }

    private function updateWalletAndClockInFail($wallet, $clockIn, $totalPay)
    {
        // Update the wallet
        $wallet->availableBalance += $totalPay;
        $wallet->heldBalance -= $totalPay;
        $wallet->save();

         // Update the clock in details
        //foreach($clockIn as $clockIn){
            $clockIn->paymentStatus = 'failed';
            $clockIn->save();
        //}


    }

    private function updatePaymentDetails($paymentDetail, $statusCode, $message, $receiptNumber, $transactionID, $transactionStatus)
    {
        // Update the payment details
        //foreach($paymentDetails as $paymentDetail){
            $paymentDetail->statusCode = $statusCode;
            $paymentDetail->message = $message;
            $paymentDetail->receiptNumber = $receiptNumber;
            $paymentDetail->transactionID = $transactionID;
            $paymentDetail->transactionStatus = $transactionStatus;
            $paymentDetail->save();
        //}


    }



    private function getPaymentDetails($payerTransactionID){
        $paymentDetails = Transactions::where('payerTransactionID', $payerTransactionID)->first();
        if (!$paymentDetails) {
            abort(400, 'Payment was not initiated');
        }

        // if ($paymentDetails->statusCode === '00') {
        //     abort(200, 'Payment already processed');
        // }
        return $paymentDetails;
    }

    private function getClockInDetails($paymentDetails){
        $clockInDates = $paymentDetails->workDate;
        $projectId = $paymentDetails->projectId;
        //$siteManagerId = $paymentDetails->siteManagerId;
        $workerId = $paymentDetails->workerId;

        $clockIns = ClockIns::where('clockInTime', $clockInDates)
                    ->where('projectId', $projectId)
                    //->where('siteManagerId', $siteManagerId)
                    ->where('workerId', $workerId)
                    ->first();

        if(!$clockIns){
            abort(400, 'Clock in details not found');
        }

        return $clockIns;
    }

    private function getWallet($siteManagerId){
        $wallet = SiteManagerWallet::where('siteManagerId', $siteManagerId)->first();
        if(!$wallet){
            abort(400, 'Wallet not found');
        }
        return $wallet;
    }
}
