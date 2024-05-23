<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advert extends Model
{
    use HasFactory;
    use SoftDeletes,CascadeSoftDeletes;
    protected $table = 'advert';
    protected $primaryKey = 'advertId';
    protected $cascadeDeletes = ['advertImages'];


    protected $fillable = [
        'advertId',
        'title',
        'image',
        'description',
        'status',
        'price',
        'date',
        'location',
        'address',
        'phone',
    ];

    public function advertImages()
    {
        return $this->hasMany(AdvertImages::class,'advertId','advertId');
    }

}
