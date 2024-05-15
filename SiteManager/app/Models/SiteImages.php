<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteImages extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'siteImages';
    protected $primaryKey = 'imageId';
    protected $dates = ['deleted_at'];


    protected $fillable = [
        'images',
        'name',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId', 'projectId');
    }

}
