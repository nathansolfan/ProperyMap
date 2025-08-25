<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PropertyService
{
    private $apiUrl = 'https://landregistry.data.gov.uk/data/ppi/transaction-record.json';

    public function getProperties($search, $sortBy = 'street_number')
    {
        $search = strtoupper(trim($search));

        // Verifica se é código postal ou busca geral
        if ($this->isPostcode($search)) {
            return $this->searchByPostcode($search, $sortBy);
        } else {
            return $this->searchByAddress($search, $sortBy);
        }
    }

    public function getPropertiesByStreet($street, $city = 'LONDON', $sortBy = 'street_number')
    {
        // Remove números do início para buscar só a rua
        $cleanStreet = $this->extractStreetName($street);

        $response = Http::get($this->apiUrl, [
            'propertyAddress.street' => strtoupper($cleanStreet),
            'propertyAddress.town' => strtoupper($city),
            '_pageSize' => '1000'
        ]);

        if (!$response->successful()) {
            return ['properties' => [], 'search' => "$street, $city", 'count' => 0, 'sortBy' => $sortBy];
        }

        $data = $response->json();
        $allProperties = $this->formatProperties($data['result']['items'] ?? [], $sortBy);

        // Se foi pesquisado um número específico, filtra
        $specificNumber = $this->extractStreetNumber($street);
        if ($specificNumber && $specificNumber !== 9999) {
            $allProperties = array_filter($allProperties, function($property) use ($specificNumber) {
                return $this->extractStreetNumber($property['address']) === $specificNumber;
            });
        }

        return [
            'properties' => array_values($allProperties),
            'search' => "$street, $city",
            'count' => count($allProperties),
            'sortBy' => $sortBy
        ];
    }

    private function searchByPostcode($postcode, $sortBy = 'street_number')
    {
        $response = Http::get($this->apiUrl, [
            'propertyAddress.postcode' => $postcode . '*',
            '_pageSize' => '1000'
        ]);

        if (!$response->successful()) {
            return ['properties' => [], 'search' => $postcode, 'count' => 0, 'sortBy' => $sortBy];
        }

        $data = $response->json();
        $properties = $this->formatProperties($data['result']['items'] ?? [], $sortBy);

        return [
            'properties' => $properties,
            'search' => $postcode,
            'count' => count($properties),
            'sortBy' => $sortBy
        ];
    }

    private function searchByAddress($search, $sortBy = 'street_number')
    {
        // Tenta extrair número e nome da rua
        $streetNumber = $this->extractStreetNumber($search);
        $streetName = $this->extractStreetName($search);
        $city = $this->extractCity($search);

        if (empty($streetName)) {
            return ['properties' => [], 'search' => $search, 'count' => 0, 'sortBy' => $sortBy];
        }

        // Busca pela rua
        $response = Http::get($this->apiUrl, [
            'propertyAddress.street' => $streetName,
            'propertyAddress.town' => $city ?: 'LIVERPOOL', // Default para Liverpool se não especificado
            '_pageSize' => '1000'
        ]);

        if (!$response->successful()) {
            return ['properties' => [], 'search' => $search, 'count' => 0, 'sortBy' => $sortBy];
        }

        $data = $response->json();
        $allProperties = $this->formatProperties($data['result']['items'] ?? [], $sortBy);

        // Se foi pesquisado um número específico, filtra
        if ($streetNumber && $streetNumber !== 9999) {
            $allProperties = array_filter($allProperties, function($property) use ($streetNumber) {
                return $this->extractStreetNumberFromAddress($property['address']) === $streetNumber;
            });
        }

        return [
            'properties' => array_values($allProperties),
            'search' => $search,
            'count' => count($allProperties),
            'sortBy' => $sortBy
        ];
    }

    private function isPostcode($search)
    {
        // Regex simples para detectar código postal UK
        return preg_match('/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s*[0-9][A-Z]{2}$|^[A-Z]{1,2}[0-9]{1,2}$/', $search);
    }

    private function extractStreetNumber($address)
    {
        if (preg_match('/^(\d+)/', trim($address), $matches)) {
            return (int) $matches[1];
        }
        return 9999;
    }

    private function extractStreetNumberFromAddress($address)
    {
        if (preg_match('/^(\d+)/', trim($address), $matches)) {
            return (int) $matches[1];
        }
        return 9999;
    }

    private function extractStreetName($address)
    {
        // Remove número do início e cidade do final
        $address = trim($address);

        // Remove número do início
        $address = preg_replace('/^\d+\s*/', '', $address);

        // Remove cidade do final (ex: ", LIVERPOOL")
        $address = preg_replace('/,\s*[A-Z]+\s*$/i', '', $address);

        return strtoupper(trim($address));
    }

    private function extractCity($address)
    {
        // Procura por cidade após vírgula
        if (preg_match('/,\s*([A-Z]+)\s*$/i', $address, $matches)) {
            return strtoupper($matches[1]);
        }
        return '';
    }

    private function formatProperties($items, $sortBy = 'street_number')
    {
        $properties = [];

        foreach ($items as $item) {
            if (!isset($item['pricePaid']) || !isset($item['propertyAddress'])) {
                continue;
            }

            $address = $item['propertyAddress'];
            $transactionDate = $item['transactionDate'] ?? '';
            $timestamp = strtotime($transactionDate);

            $properties[] = [
                'price' => (int) $item['pricePaid'],
                'address' => $this->formatAddress($address),
                'postcode' => $address['postcode'] ?? 'N/A',
                'date' => $this->formatDate($transactionDate),
                'type' => $this->getPropertyType($item),
                'street_number' => $this->extractStreetNumberFromFullAddress($address),
                'raw_date' => $transactionDate,
                'timestamp' => $timestamp ? $timestamp : 0
            ];
        }

        $properties = $this->removeDuplicates($properties);

        // ORDENAÇÃO CORRIGIDA - agora funciona corretamente
        if ($sortBy === 'date') {
            // Ordena por data (mais recente primeiro), depois por número da rua
            usort($properties, function($a, $b) {
                // Primeiro critério: data (mais recente primeiro)
                if ($a['timestamp'] !== $b['timestamp']) {
                    return $a['timestamp'] - $b['timestamp']; // Mais antigo primeiro
                }

                // Segundo critério: número da rua (crescente)
                if ($a['street_number'] !== $b['street_number']) {
                    return $a['street_number'] - $b['street_number'];
                }

                // Terceiro critério: preço (maior primeiro)
                return $b['price'] - $a['price'];
            });
        } else {
            // Ordenação padrão: primeiro por número da rua, depois por data mais recente
            usort($properties, function($a, $b) {
                // Primeiro critério: número da rua (crescente)
                if ($a['street_number'] !== $b['street_number']) {
                    return $a['street_number'] - $b['street_number'];
                }

                // Segundo critério: data (mais recente primeiro para o mesmo endereço)
                if ($a['timestamp'] !== $b['timestamp']) {
                    return $a['timestamp'] - $b['timestamp']; // Mais antigo primeiro
                }

                // Terceiro critério: preço (maior primeiro)
                return $b['price'] - $a['price'];
            });
        }

        // Remove propriedades auxiliares antes de retornar
        foreach ($properties as &$property) {
            unset($property['street_number']);
            unset($property['raw_date']);
            unset($property['timestamp']);
        }

        return $properties;
    }

    private function extractStreetNumberFromFullAddress($address)
    {
        $fullAddress = '';

        if (!empty($address['saon'])) $fullAddress .= ' ' . $address['saon'];
        if (!empty($address['paon'])) $fullAddress .= ' ' . $address['paon'];

        $fullAddress = trim($fullAddress);

        if (preg_match('/^(\d+)/', $fullAddress, $matches)) {
            return (int) $matches[1];
        }

        return 9999;
    }

    private function formatAddress($address)
    {
        $parts = [];
        if (!empty($address['saon'])) $parts[] = trim($address['saon']);
        if (!empty($address['paon'])) $parts[] = trim($address['paon']);
        if (!empty($address['street'])) $parts[] = trim($address['street']);
        return implode(' ', array_filter($parts));
    }

    private function formatDate($dateString)
    {
        if (empty($dateString)) return 'N/A';
        try {
            // Garante que a data seja convertida corretamente
            $timestamp = strtotime($dateString);
            if ($timestamp === false) return 'N/A';
            return date('d/m/Y', $timestamp);
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    private function getPropertyType($item)
    {
        if (isset($item['propertyType']['prefLabel'][0]['_value'])) {
            return ucfirst(str_replace('-', ' ', $item['propertyType']['prefLabel'][0]['_value']));
        }
        return 'Unknown';
    }

    private function removeDuplicates($properties)
    {
        $unique = [];
        $seen = [];

        foreach ($properties as $property) {
            $key = $property['address'] . '|' . $property['price'] . '|' . $property['date'];
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $unique[] = $property;
            }
        }

        return $unique;
    }
}
