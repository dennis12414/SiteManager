<?php

namespace App\Models;
// INSERT INTO siteManagers (name, email, phoneNumber, otp, password, deleted_at, remember_token, phoneVerified)
// VALUES ('Derrick', 'testemail@gmail.com', '0712345678', '1234', 'password123', NULL, NULL, true);

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
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
        return $this->belongsToMany(Project::class, 'project_siteManager', 'siteManager_id', 'project_id');
    }

    public function siteManagerWallet()
    {
        return $this->hasOne(SiteManagerWallet::class, 'siteManagerId');
    }

    // public function siteManagerWalletTransactions()
    // {
    //     return $this->hasMany(SiteManagerWalletTransaction::class, 'siteManagerId');
    // }



}
