<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PropertyService
{
    public function getProperties($postcode = 'L1')
    {
        $response = Http::get('https://landregistry.data.gov.uk/data/ppi/transaction-record.json', [
            'propertyAddress.postcode' => $postcode . '*',
            '_pageSize' => '100'
        ]);

        if (!$response->successful()){
            return [];
        }

        $data = $response->json();
        return $data['result']['items'] ?? [];

    }

}
