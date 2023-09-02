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

            // Get payment rate, site manager wallet, and clock in details
            $payRate = $paymentDetails->payRate;

            $wallet = $this->getWallet($paymentDetails->siteManagerId);
            $clockIn = $this->getClockInDetails($paymentDetails->projectId, $paymentDetails->workerId, $paymentDetails->workDate);

            // Update wallet and clock in details based on payment status
            if ($statusCode === "00") {
                $transactionStatus = "Success";
                $this->updateWalletAndClockInSuccess($wallet, $clockIn, $payRate);
                $this->updatePaymentDetails($paymentDetails, $statusCode, $message, $providerNarration, $receiptNumber, $transactionID, $transactionStatus);
            }else{
                $transactionStatus = "Failed";
                $this->updateWalletAndClockInFail($wallet, $clockIn, $payRate);
                $this->updatePaymentDetails($paymentDetails, $statusCode, $message, $providerNarration, $receiptNumber, $transactionID, $transactionStatus);
            }

            // Return success response
            return response([
                'message' => 'Payment processed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response([
                'message' => $e->getMessage(),
                
            ], 400);
        }
    }

    private function updateWalletAndClockInSuccess($wallet, $clockIn, $payRate)
    {
        // Update the wallet
        $wallet->heldBalance -= $payRate;
        $wallet->balance -= $payRate;
        $wallet->save();

        // Update the clock in details
        $clockIn->paymentStatus = 'paid';
        $clockIn->amountPaid = $payRate;
        $clockIn->save();
    }

    private function updatePaymentDetails($paymentDetails, $statusCode, $message, $providerNarration, $receiptNumber, $transactionID,$transactionStatus)
    {
        // Update the payment details
        $paymentDetails->statusCode = $statusCode;
        $paymentDetails->message = $message;
        $paymentDetails->receiptNumber = $receiptNumber;
        $paymentDetails->transactionID = $transactionID;
        $paymentDetails->transactionStatus = $transactionStatus;
        $paymentDetails->save();
    }

    private function updateWalletAndClockInFail($wallet, $clockIn, $payRate)
    {
        // Update the wallet
        $clockIn->paymentStatus = 'failed';
        $clockIn->save();

        $wallet->availableBalance += $payRate;
        $wallet->heldBalance -= $payRate;
        $wallet->save();
    }

    private function getPaymentDetails($payerTransactionID){
        $paymentDetails = Transactions::where('payerTransactionID', $payerTransactionID)->first();
        if (!$paymentDetails) {
            abort(400, 'Payment was not initiated');
        }

        if ($paymentDetails->statusCode === '00') {
            abort(200, 'Payment already processed');
        }
        return $paymentDetails;
    }

    private function getClockInDetails($projectId, $workerId, $workDate){
        $clockIn = ClockIns::where('projectId', $projectId)
            ->where('workerId', $workerId)
            ->where('clockInTime', $workDate)
            ->first();
        if(!$clockIn){
            abort(400, 'Clock in details not found');
        }
        return $clockIn;
    }

    private function getWallet($siteManagerId){
        $wallet = SiteManagerWallet::where('siteManagerId', $siteManagerId)->first();
        if(!$wallet){
            abort(400, 'Wallet not found');
        }
        return $wallet;
    }
}
