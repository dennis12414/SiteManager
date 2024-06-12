<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use App\Models\Transactions;
use App\Models\paymentTransactions;
use App\Models\MpesaTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Worker;
use Carbon\Carbon;

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

   public function getTransactionHistory(string $phoneNumber, string $startDate = null,string $endDate = null, string $paymentType = null, string $paymentStatus = null,string $projectId = null)
    {

        $startDate = request('startDate');
        $endDate = request('endDate');


        $paymentType = request('paymentType');
        $paymentStatus = request('paymentStatus');
        $projectId = request('projectId');
//

        if ($startDate && $endDate) {
            $startDate = $startDate . ' 00:00:00';
            $endDate = $endDate . ' 23:59:59';
        }

        $siteManager = SiteManager::where('phoneNumber', $phoneNumber)
                    ->where('phoneVerified', true)
                    ->first();

        if(!$siteManager){
            return response([
                'message' => 'Site manager does not exist',
            ], 404);
        }

        $query = Transactions::where('siteManagerId', $siteManager->siteManagerId)
            ->when($paymentType, function($query) use ($paymentType){
                return $query->where('payType',$paymentType);
            })
            ->when($paymentStatus, function($query) use ($paymentStatus){
                return $query->where('transactionStatus',$paymentStatus);
            })
            ->when($projectId, function($query) use ($projectId){
                return $query->where('projectId',$projectId);
            })
            ->when($startDate && $endDate == null, function ($query) use ($startDate) {
                return $query->whereDate('created_at', $startDate);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc');

         $transactions = $query->get();

         foreach($transactions as $transaction){
            if($transaction->workerId){
                $worker = Worker::where('workerId', $transaction->workerId)->first();
                $transaction->workerName = $worker->name;
                $transaction->workerPhoneNumber = $worker->phoneNumber;
            }

         }

        // if($paymentType === 'pay') {
        //     foreach($transactions as $transaction){
        //         $worker = Worker::where('workerId', $transaction->workerId)->first();
        //         $transaction->workerName = $worker->name;
        //     }
        // }

//        $transactions = $transactions->map(function ($transaction) {
//            return array_filter($transaction->toArray(), function ($value) {
//                return !is_null($value);
//            });
//        });

        return response([
            'transactions'=> $transactions,
        ],200);
    }

}
