<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Postcode extends Model
{
    protected $fillable =  [
        'postcode',
//        'district',
//        'avg_price',
//        'property_count',
//        'latitude',
//        'longitude',
//        'created_at',
//        'updated_at'
        ];

    public function properties()
//        w/out : HasMany - IDE error but works
    {
        return $this->hasMany(Property::class);
    }
}
