<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertiser extends Model
{
    use HasFactory;
    use SoftDeletes,CascadeSoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'advertiser';
    protected $primaryKey = 'advertiserId';
    protected $cascadeDeletes = ['adverts'];

    protected $fillable = [
        'advertiserId',
        'name',
        'email',
        'phone',
        'address',
        'image'
    ];



    public function adverts(){
        return $this->hasMany(Advert::class,'advertiserId','advertiserId');
    }



}
