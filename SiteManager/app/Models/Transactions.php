<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $table ='Transactions';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'payType',
        'statusCode',
        'partnerReferenceID',
        'transactionID',
        'message',
        'narration',
        'partnerTransactionID',
        'payerTransactionID',
        'receiptNumber',
        'siteManagerId',
        'workerId',
        'workDate',
        'projectId',
        'payRate',
        'phoneNumber',
        'transactionAmount',
        'transactionStatus',
    ];
}
