<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClockIns extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'clockIns';
    protected $primaryKey = 'clockId';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'clockId',
        'siteManagerId',
        'projectId',
        'workerId',
        'clockInTime',
        'clockOutTime',
        'date',
        'amountPaid',
        'paymentStatus',
    ];
    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId', 'projectId');
    }
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workerId', 'workerId');
    }
}
