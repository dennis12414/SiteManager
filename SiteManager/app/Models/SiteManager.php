<?php

namespace App\Models;
// INSERT INTO siteManagers (name, email, phoneNumber, otp, password, deleted_at, remember_token, phoneVerified) 
// VALUES ('Derrick', 'testemail@gmail.com', '0712345678', '1234', 'password123', NULL, NULL, true);

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteManager extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;
    protected $table = 'siteManagers';
    protected $dates = ['deleted_at'];

    protected $primaryKey = 'siteManagerId';

    protected $fillable = [
        'name',
        'email',
        'phoneNumber',
        'otp',
        'password',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'siteManagerId');
    }
    
}
