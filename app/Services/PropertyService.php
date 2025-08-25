<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PropertyService
{
    private $apiUrl = 'https://landregistry.data.gov.uk/data/ppi/transaction-record.json';

    public function getProperties($search, $sortBy = 'street_number')
    {
        $search = strtoupper(trim($search));

        // Detecta se é postcode ou endereço
        if (preg_match('/^[A-Z]{1,2}[0-9]{1,2}/', $search)) {
            $params = ['propertyAddress.postcode' => $search . '*'];
        } else {
            $streetName = preg_replace('/^\d+\s*/', '', $search);
            $streetName = preg_replace('/,\s*[A-Z]+\s*$/i', '', $streetName);
            $city = preg_match('/,\s*([A-Z]+)\s*$/i', $search, $matches) ? $matches[1] : 'LIVERPOOL';

            $params = [
                'propertyAddress.street' => strtoupper(trim($streetName)),
                'propertyAddress.town' => strtoupper($city)
            ];
        }

        $response = Http::get($this->apiUrl, array_merge($params, ['_pageSize' => '1000']));

        if (!$response->successful()) {
            return ['properties' => [], 'search' => $search, 'count' => 0, 'sortBy' => $sortBy];
        }

        $properties = $this->formatProperties($response->json()['result']['items'] ?? [], $sortBy);

        return [
            'properties' => $properties,
            'search' => $search,
            'count' => count($properties),
            'sortBy' => $sortBy
        ];
    }

    public function getPropertiesByStreet($street, $city = 'LONDON', $sortBy = 'street_number')
    {
        return $this->getProperties("$street, $city", $sortBy);
    }

    private function formatProperties($items, $sortBy)
    {
        $properties = [];
        $seen = [];

        foreach ($items as $item) {
            if (!isset($item['pricePaid'], $item['propertyAddress'])) continue;

            $address = $item['propertyAddress'];
            $date = $item['transactionDate'] ?? '';
            $timestamp = strtotime($date) ?: 0;

            // Extrai número da rua
            $fullAddress = trim(($address['saon'] ?? '') . ' ' . ($address['paon'] ?? ''));
            $streetNumber = preg_match('/^(\d+)/', $fullAddress, $matches) ? (int)$matches[1] : 9999;

            $property = [
                'price' => (int) $item['pricePaid'],
                'address' => trim(implode(' ', array_filter([
                    $address['saon'] ?? '',
                    $address['paon'] ?? '',
                    $address['street'] ?? ''
                ]))),
                'postcode' => $address['postcode'] ?? 'N/A',
                'date' => $timestamp ? date('d/m/Y', $timestamp) : 'N/A',
                'type' => ucfirst(str_replace('-', ' ',
                    $item['propertyType']['prefLabel'][0]['_value'] ?? 'unknown'
                )),
                'street_number' => $streetNumber,
                'timestamp' => $timestamp
            ];

            // Remove duplicatas
            $key = $property['address'] . '|' . $property['price'] . '|' . $property['date'];
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $properties[] = $property;
            }
        }

        // Ordenação
        usort($properties, function($a, $b) use ($sortBy) {
            if ($sortBy === 'date') {
                return $a['timestamp'] != $b['timestamp']
                    ? $a['timestamp'] - $b['timestamp']  // Mais antigo primeiro
                    : $a['street_number'] - $b['street_number'];
            } else {
                return $a['street_number'] != $b['street_number']
                    ? $a['street_number'] - $b['street_number']  // Por número da rua
                    : $a['timestamp'] - $b['timestamp'];  // Mais antigo primeiro para mesmo endereço
            }
        });

        // Remove campos auxiliares
        foreach ($properties as &$property) {
            unset($property['street_number'], $property['timestamp']);
        }

        return $properties;
    }
}
