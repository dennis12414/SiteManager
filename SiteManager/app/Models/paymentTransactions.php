<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paymentTransactions extends Model
{
    use HasFactory;
    protected $table = 'paymentTransactions';
    protected $primaryKey = 'paymentTransactionId';
}
