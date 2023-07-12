<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $primaryKey = 'workerId';
    protected static function newFactory()
    {
        return \App\Database\Eloquent\WorkerFactory::new();
    }

    protected $fillable = [
        'name',
        'phoneNumber',
        'dateRegistered',
        'payRate',
        'siteManagerId',
    ];
}
