<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\SiteManagerWallet;
use App\Models\MpesaTransaction;
use Illuminate\Http\Request;

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
        }
        $walletBalance =  $wallet-> balance;

        return response([
            'message' => 'Wallet balance',
            'balance' => $walletBalance
        ], 200);
  }

//   public function getWalletTransactions(string $phoneNumber, string $startDate = null, string $endDate = null){
        
//         $startDate = request('startDate');
//         $endDate = request('endDate');

//         $siteManager = SiteManager::where('phoneNumber', $phoneNumber)
//                         ->where('phoneVerified', true)
//                         ->first();
//         if(!$siteManager){
//             return response([
//                 'phoneNumber' => $phoneNumber,
//                 'message' => 'Site manager does not exist',
//             ], 404);
//         }

//         if(substr($phoneNumber, 0, 1) == '0'){ 
//             $phoneNumber = '254' . substr($phoneNumber, 1);
//         }
//         $phoneNumber = str_replace('+', '', $phoneNumber);
//         $phoneNumber = str_replace(' ', '', $phoneNumber);
       

//         if($startDate && $endDate){
//             $startDate = $startDate . ' 00:00:00';
//             $endDate = $endDate . ' 23:59:59';
//             $transactions = MpesaTransaction::where('phoneNumber', $phoneNumber)
//                             ->whereBetween('transactionDate', [$startDate, $endDate])
//                             ->orderBy('transactionDate', 'desc')
//                             ->get();
//         }
//         elseif($startDate){
            
//             $transactions = MpesaTransaction::where('phoneNumber', $phoneNumber)
//                             ->whereBetween('transactionDate', [$startDate . ' 00:00:00', $startDate . ' 23:59:59'])
//                             ->orderBy('transactionDate', 'desc')
//                             ->get();
//         }
//         else{
            
//             $transactions = MpesaTransaction::where('phoneNumber', $phoneNumber)
//                             ->orderBy('transactionDate', 'desc')
//                             ->get();     
//         }

//         return response([
//             'message' => 'Transactions',
//             'transactions' => $transactions
//         ], 200);
//   }
}
