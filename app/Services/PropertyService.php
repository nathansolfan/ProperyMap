<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PropertyService
{
    private $cityMap = [
        // Mapeamento inteligente
        'L' => 'LIVERPOOL',
        'M' => 'MANCHESTER',
        'B' => 'BIRMINGHAM',
        'S' => 'SHEFFIELD',
        'LS' => 'LEEDS',
        'NE' => 'NEWCASTLE',
        'E' => 'LONDON',
        'N' => 'LONDON',
        'SW' => 'LONDON',
        'SE' => 'LONDON',
        'W' => 'LONDON',
        'NW' => 'LONDON'
    ];

    public function getProperties($search)
    {
        $search = strtoupper(trim($search));

        // Se é cidade conhecida, busca direto
        if ($this->isKnownCity($search)) {
            return $this->searchByCity($search);
        }

        // Se é postcode, mapeia para cidade
        $city = $this->mapPostcodeToCity($search);
        if ($city) {
            $result = $this->searchByCity($city);

            // SEMPRE filtra para qualquer postcode (incluindo L4, M1, etc.)
            $result['properties'] = $this->filterByPostcode($result['properties'], $search);
            $result['search'] = $search . " (filtered from " . $city . " area)";
            $result['filtered'] = true;
            $result['count'] = count($result['properties']); // ← Atualiza o contador após filtrar

            return $result;
        }

        // Fallback para busca direta
        return $this->searchByCity($search);
    }

    private function isKnownCity($search)
    {
        $cities = ['LONDON', 'MANCHESTER', 'BIRMINGHAM', 'LIVERPOOL', 'LEEDS', 'SHEFFIELD', 'NEWCASTLE', 'BRISTOL'];
        return in_array($search, $cities);
    }

    private function mapPostcodeToCity($postcode)
    {
        // Remove espaços e números
        $cleanPostcode = preg_replace('/[^A-Z]/', '', $postcode);

        // Tenta 2 caracteres primeiro, depois 1
        $twoChar = substr($cleanPostcode, 0, 2);
        if (isset($this->cityMap[$twoChar])) {
            return $this->cityMap[$twoChar];
        }

        $oneChar = substr($cleanPostcode, 0, 1);
        return $this->cityMap[$oneChar] ?? null;
    }

    private function filterByPostcode($properties, $searchPostcode)
    {
        // Remove espaços e fica só com letras/números
        $searchClean = preg_replace('/[^A-Z0-9]/', '', $searchPostcode);

        return array_filter($properties, function($property) use ($searchClean) {
            $propertyPostcode = preg_replace('/[^A-Z0-9]/', '', $property['postcode']);

            // Busca exata pelos caracteres do search
            return strpos($propertyPostcode, $searchClean) === 0;
        });
    }

    private function searchByCity($city)
    {
        $response = Http::get('https://landregistry.data.gov.uk/data/ppi/transaction-record.json', [
            'propertyAddress.town' => strtoupper($city),
            '_pageSize' => '1000'
        ]);

        if (!$response->successful()) {
            return ['properties' => [], 'search' => $city, 'count' => 0];
        }

        $data = $response->json();
        $items = $data['result']['items'] ?? [];

        return [
            'properties' => $this->formatProperties($items),
            'search' => $city,
            'count' => count($items);
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
