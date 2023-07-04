<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SiteManager extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'siteManagers';

    protected $primaryKey = 'siteManagerId';

    protected $fillable = [
        'name',
        'email',
        'phoneNumber',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'siteManagerId');
    }
    
}
