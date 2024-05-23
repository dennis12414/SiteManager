<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertImages extends Model
{
    use HasFactory;
    protected $table = 'advertImages';
    protected $primaryKey = 'advertImageId';

    protected $fillable = [
        'advertImageId',
        'advertId',
        'name'
    ];

    public function advert(){
        return $this->belongsTo(Advert::class, 'advertId');
    }

}
