<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockIns extends Model
{
    use HasFactory;

    protected $table = 'clockIns';

    protected $fillable = [
        'siteManagerId',
        'projectId',
        'workerId',
        'clockInTime',
        'clockOutTime',
        'date',
    ];
}
