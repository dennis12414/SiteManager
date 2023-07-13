<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $primaryKey = 'workerId';

    protected $fillable = [
        'name',
        'phoneNumber',
        'dateRegistered',
        'payRate',
        'siteManagerId',
    ];
}
