<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadWalletsTransaction extends Model
{
    use HasFactory;
    protected $table ='loadWalletsTransactions';
    protected $primaryKey = 'loadTransactionId';

    protected $fillable = [
        'partnerReferenceID',
        'transactionID',
        'message',
        'statusCode',
        'partnerTransactionID',
        'payerTransactionID',
        'receiptNumber',
        'siteManagerId',
        'phoneNumber',
        'transactionAmount',
        'transactionStatus',
    ];
}
