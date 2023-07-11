<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
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
