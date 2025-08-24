<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PropertyService
{
    // Busca por postcode
    public function getProperties($postcode)
    {
        $postcode = strtoupper(trim($postcode));

        $response = Http::get('https://landregistry.data.gov.uk/data/ppi/transaction-record.json', [
            'propertyAddress.postcode' => $postcode . '*',
            '_pageSize' => '1000'
        ]);

        if (!$response->successful()) {
            return ['properties' => [], 'search' => $postcode, 'count' => 0];
        }

        $data = $response->json();
        $properties = $this->formatProperties($data['result']['items'] ?? []);

        return [
            'properties' => $properties,
            'search' => $postcode,
            'count' => count($properties)
        ];
    }

    // Busca por rua
    public function getPropertiesByStreet($street, $city = 'LONDON')
    {
        $response = Http::get('https://landregistry.data.gov.uk/data/ppi/transaction-record.json', [
            'propertyAddress.street' => strtoupper($street),
            'propertyAddress.town' => strtoupper($city),
            '_pageSize' => '1000'
        ]);

        if (!$response->successful()) {
            return ['properties' => [], 'search' => "$street, $city", 'count' => 0];
        }

        $data = $response->json();
        $properties = $this->formatProperties($data['result']['items'] ?? []);

        return [
            'properties' => $properties,
            'search' => "$street, $city",
            'count' => count($properties)
        ];
    }

    private function formatProperties($items)
    {
        $properties = [];

        foreach ($items as $item) {
            $properties[] = [
                'price' => $item['pricePaid'] ?? 0,
                'address' => $this->formatAddress($item['propertyAddress'] ?? []),
                'postcode' => $item['propertyAddress']['postcode'] ?? '',
                'date' => $this->formatDate($item['transactionDate'] ?? ''),
                'type' => $this->getPropertyType($item)
            ];
        }

        // Ordena por data (mais recente primeiro)
        usort($properties, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $properties;
    }

    private function formatAddress($address)
    {
        $parts = [];
        if (!empty($address['saon'])) $parts[] = $address['saon'];
        if (!empty($address['paon'])) $parts[] = $address['paon'];
        if (!empty($address['street'])) $parts[] = $address['street'];
        return implode(' ', $parts);
    }

    private function formatDate($dateString)
    {
        if (empty($dateString)) return '';
        return date('d/m/Y', strtotime($dateString));
    }

    private function getPropertyType($item)
    {
        if (isset($item['propertyType']['prefLabel'][0]['_value'])) {
            return ucfirst(str_replace('-', ' ', $item['propertyType']['prefLabel'][0]['_value']));
        }
        return 'Unknown';
    }
}
