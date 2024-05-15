<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoadWalletsTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
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
