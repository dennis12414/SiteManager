<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteManagerWallet extends Model
{
    use HasFactory;
    protected $table = 'wallets';
    protected $primaryKey = 'walletId';

    protected $fillable = [
        'siteManagerId',
        'phoneNumber',
        'balance',
    ];
    
}





