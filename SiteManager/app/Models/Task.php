<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'tasks';
    protected $primaryKey = 'taskId';

    protected $fillable = [
        'title',
        'budget',
        'start_date',
        'projectId',
        'end_date',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(Worker::class, 'task_worker', 'task_id', 'worker_id');
    }
}
