<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use App\Models\LoadWalletsTransaction;
use App\Models\paymentTransactions;
use App\Models\MpesaTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Worker;

class WalletController extends Controller
{
    public function getWalletBalance(string $phoneNumber){
      
        $siteManager = SiteManager::where('phoneNumber', $phoneNumber)
                        ->where('phoneVerified', true)
                        ->first();

        if(!$siteManager){
            return response([
                'message' => 'Site manager does not exist',
            ], 404);
        }

        $wallet = SiteManagerWallet::where('phoneNumber', $phoneNumber)->first();
        if(!$wallet){
            $wallet = SiteManagerWallet::create([
                'siteManagerId' => $siteManager->siteManagerId,
                'phoneNumber' => $siteManager->phoneNumber,
                'balance' => 0,
                'availableBalance' => 0,
                'heldBalance' =>0,
            ]);

            return response([
                'message' => 'Wallet balance',
                'balance' => 0
            ], 200);
        }
        $walletBalance =  $wallet-> balance;

        return response([
            'message' => 'Wallet balance',
            'balance' => $walletBalance
        ], 200);
  }

   public function getTransactionHistory(string $phoneNumber, string $startDate = null,string $endDate = null,string $paymentType = null)
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $paymentType = request('paymentType');



        $siteManager = SiteManager::where('phoneNumber', $phoneNumber)
                    ->where('phoneVerified', true)
                    ->first();

        if(!$siteManager){
            return response([
                'message' => 'Site manager does not exist',
            ], 404);
        }

        if ($paymentType === 'load') {
            $query = LoadWalletsTransaction::select('loadTransactionId', 'siteManagerId', 'transactionAmount', 'transactionStatus','message', 'created_at as date')
                ->where('siteManagerId', $siteManager->siteManagerId)
                ->orderBy('date', 'desc');

         } elseif ($paymentType === 'pay') {
            $query = PaymentTransactions::select('paymentTransactionId','siteManagerId', 'workerId', 'projectId', 'payRate', 'statusCode','message', 'workDate','created_at as date')
                ->where('siteManagerId', $siteManager->siteManagerId)
                ->orderBy('date', 'desc');


                
        } else {
            $loadTransactions = LoadWalletsTransaction::select('loadTransactionId','siteManagerId', 'transactionAmount', 'transactionStatus','message', 'created_at as date')
                    ->where('siteManagerId', $siteManager->siteManagerId)
                    ->when($startDate, function ($query) use ($startDate) {
                        return $query->where('date', $startDate);
                    })
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->get();

            $paymentTransactions = PaymentTransactions::select('paymentTransactionId','siteManagerId', 'workerId', 'projectId', 'payRate', 'statusCode','message', 'workDate', 'created_at as date')
                    ->where('siteManagerId', $siteManager->siteManagerId)
                    ->when($startDate, function ($query) use ($startDate) {
                        return $query->where('date', $startDate);
                    })
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->get();

                foreach($paymentTransactions as $transaction){
                    $worker = Worker::where('workerId', $transaction->workerId)->first();
                    $transaction->workerName = $worker->name;
            }

            $transactions = $loadTransactions->concat($paymentTransactions)->sortByDesc('date');
            return response([
                'transactions'=> $transactions,
            ],200);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if($startDate && $endDate){
            $startDate = $startDate . ' 00:00:00';
            $endDate = $endDate . ' 23:59:59';
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $transactions = $query->get();

        if($paymentType === 'pay') {
            foreach($transactions as $transaction){
                $worker = Worker::where('workerId', $transaction->workerId)->first();
                $transaction->workerName = $worker->name;
            }
        }

        return response([
            'transactions'=> $transactions,
        ],200);
    }

}
