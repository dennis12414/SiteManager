<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_name',
        'project_description',
        'start_date',
        'end_date',
        'site_manager_id',

    ];
    
    public function siteManager()
    {
        return $this->belongsTo(SiteManager::class, 'site_manager_id');
    }


}
