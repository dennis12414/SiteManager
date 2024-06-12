<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes,CascadeSoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'projects';
    protected $primaryKey = 'projectId';
    protected $cascadeDeletes = ['tasks','images','clockIns'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'projectName',
        'projectDescription',
        'startDate',
        'endDate',
        'siteManagerId',
        'progress',
        'status',
        'image',
        'budget',
        'inviteCode',
    ];

    function getCustomField(Project $project)
    {
        // Logic to calculate or generate the custom field value
        return 'Calculated Value';
    }
    public function siteManagers()
    {
        return $this->belongsToMany(SiteManager::class, 'project_siteManager', 'project_id', 'siteManager_id');
    }

//    public function siteManager()
//    {
//        return $this->belongsTo(SiteManager::class, 'siteManagerId');
//    }

    public function workers()
    {
        return $this->belongsToMany(Worker::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class,'projectId','projectId');
    }

    public function clockIns()
    {
        return $this->hasMany(ClockIns::class,'projectId','projectId');
    }

    public function images()
    {
        return $this->hasMany(SiteImages::class, 'projectId','projectId');
    }


}
