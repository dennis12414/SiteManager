<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Worker extends Model
{
    use HasFactory;
    use SoftDeletes, CascadeSoftDeletes;
   protected $dates = ['deleted_at'];

    protected $primaryKey = 'workerId';

    protected $cascadeDeletes = ['clockIns','tasks'];

    protected $fillable = [
        'name',
        'phoneNumber',
        'dateRegistered',
        'payRate',
        'role',
        'gender',
        'profilePic',
        'siteManagerId',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_worker', 'worker_id', 'task_id');
    }

    public function clockIns()
    {
        return $this->hasMany(ClockIns::class, 'workerId', 'workerId');
    }
}
