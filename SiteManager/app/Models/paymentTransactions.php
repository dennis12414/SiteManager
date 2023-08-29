<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paymentTransactions extends Model
{
    use HasFactory;
    protected $table = 'paymentTransactions';
    protected $primaryKey = 'paymentTransactionId';

    protected $fillable = [
        'workDate',
        'siteManagerId',
        'workerId',
        'projectId',
        'payRate',
        'partnerTransactionID',
        'receiptNumber',
        'payerTransactionID',
        'statusCode',
        'message',
    ];


}
