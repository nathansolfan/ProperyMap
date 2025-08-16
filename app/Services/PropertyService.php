<?php

namespace App\Services;

class PropertyService
{

    public function liverpoolData()
    {

    }

    public function getPriceByYear()
    {
        $areas = [
            'L1' => [
                'name' => 'City Centre',
                'lat' => 53.4084,
                'lng' => -2.9916,
                'base_price' => 280000
            ],
            'L2' => [
                'name' => 'Business District',
                'lat' => 53.4094,
                'lng' => -2.9856,
                'base_price' => 250000
            ]
        ];

        return $areas;

    }


}
