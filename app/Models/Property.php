<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
//        'id',
        'address',
        'postcode',
        'price',
        'property_type',
        'date_sold',
        'latitude',
        'longitude',
//        'created_at',
//        'updated_at'
    ];


    public function belongsTo()
    {
        return $this->belongsTo(Postcode::class);

    }
}
