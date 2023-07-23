<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'projects';
    protected $primaryKey = 'projectId';

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

    ];
    
    public function siteManager()
    {
        return $this->belongsTo(SiteManager::class, 'siteManagerId');
    }




}
