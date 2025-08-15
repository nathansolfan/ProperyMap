<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postcode extends Model
{
    protected $fillable =  [
        'postcode',
        'district',
        'avg_price',
        'property_count',
        'latitude',
        'longitude',
        'created_at',
        'updated_at'
        ];

    public function hasMany()
    {
        return $this->hasMany(Property::class);
    }
}
