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

    protected $primaryKey = 'site_manager_id';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'site_manager_id');
    }
    
}
