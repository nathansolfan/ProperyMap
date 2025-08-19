<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestApiService
{
    private $apiKey;
    private $baseUrl = 'https://use-land-property-data.service.gov.uk/api/v1';

    public function __construct()
    {
        $this->apiKey = env('HM_LAND_REGISTRY_API_KEY');
    }

    /**
     * Testar conexão básica com o API
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json'
            ])->get($this->baseUrl);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Listar datasets disponíveis
     */
    public function getAvailableDatasets()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json'
            ])->get($this->baseUrl . '/datasets');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'error' => 'Failed to fetch datasets',
                'status' => $response->status(),
                'body' => $response->body()
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Testar diferentes endpoints
     */
    public function testAllEndpoints()
    {
        $endpoints = [
            'datasets' => '/datasets',
            'ccod' => '/datasets/ccod',
            'ocod' => '/datasets/ocod',
            'nps' => '/datasets/nps',
        ];

        $results = [];

        foreach ($endpoints as $name => $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json'
                ])->timeout(10)->get($this->baseUrl . $endpoint);

                $results[$name] = [
                    'url' => $this->baseUrl . $endpoint,
                    'status' => $response->status(),
                    'success' => $response->successful(),
                    'data' => $response->successful() ? $response->json() : $response->body()
                ];

            } catch (\Exception $e) {
                $results[$name] = [
                    'url' => $this->baseUrl . $endpoint,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Testar API pública para dados de preços
     */
    public function testPublicPriceAPI($postcode = 'L1', $limit = 10)
    {
        try {
            $urls = [
                'Option 1' => [
                    'url' => 'https://landregistry.data.gov.uk/data/ppi/transaction-record.csv',
                    'params' => ['propertyAddress.postcode' => $postcode . '*']
                ],
                'Option 2' => [
                    'url' => 'https://landregistry.data.gov.uk/data/ppi/transaction-record.csv',
                    'params' => ['postcode' => $postcode]
                ],
                'Option 3' => [
                    'url' => 'https://landregistry.data.gov.uk/data/ppi/transaction-record.csv',
                    'params' => ['propertyAddress.town' => 'LIVERPOOL']
                ]
            ];

            $results = [];

            foreach ($urls as $name => $config) {
                try {
                    $response = Http::timeout(15)->get($config['url'], $config['params']);

                    $results[$name] = [
                        'url' => $config['url'] . '?' . http_build_query($config['params']),
                        'status' => $response->status(),
                        'success' => $response->successful(),
                        'data_preview' => substr($response->body(), 0, 500),
                        'content_length' => strlen($response->body())
                    ];
                } catch (\Exception $e) {
                    $results[$name] = [
                        'url' => $config['url'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'results' => $results,
                'note' => 'Testing different parameter formats'
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Buscar dados por postcode - formato JSON
     */
    public function getPropertyPrices($postcode = 'L1')
    {
        try {
            $attempts = [
                'JSON with postcode' => [
                    'url' => 'https://landregistry.data.gov.uk/data/ppi/transaction-record.json',
                    'params' => ['propertyAddress.postcode' => $postcode . '*']
                ],
                'JSON with town' => [
                    'url' => 'https://landregistry.data.gov.uk/data/ppi/transaction-record.json',
                    'params' => ['propertyAddress.town' => 'LIVERPOOL']
                ]
            ];

            $results = [];

            foreach ($attempts as $name => $config) {
                try {
                    $response = Http::timeout(15)->get($config['url'], $config['params']);

                    if ($response->successful()) {
                        $json = $response->json();
                        $results[$name] = [
                            'url' => $config['url'] . '?' . http_build_query($config['params']),
                            'status' => $response->status(),
                            'success' => true,
                            'data_type' => gettype($json),
                            'record_count' => is_array($json) ? count($json) : 'Not array',
                            'sample_data' => is_array($json) && count($json) > 0 ? array_slice($json, 0, 2) : $json
                        ];
                    } else {
                        $results[$name] = [
                            'url' => $config['url'],
                            'status' => $response->status(),
                            'error' => $response->body()
                        ];
                    }
                } catch (\Exception $e) {
                    $results[$name] = [
                        'url' => $config['url'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'attempts' => $results,
                'note' => 'Testing different JSON approaches'
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Buscar dados reais de Liverpool
     */
    public function getLiverpoolData()
    {
        try {
            $response = Http::timeout(30)->get('https://landregistry.data.gov.uk/data/ppi/transaction-record.json', [
                'propertyAddress.town' => 'LIVERPOOL',
                '_pageSize' => '50'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'total_records' => count($data),
                    'data_structure' => gettype($data),
                    'full_data' => $data,
                    'note' => 'Real Liverpool property data!'
                ];
            }

            return [
                'error' => 'Failed to fetch Liverpool data',
                'status' => $response->status(),
                'body' => $response->body()
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Verificar status do API key
     */
    public function checkApiKeyStatus()
    {
        return [
            'api_key_configured' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey ?? ''),
            'api_key_format' => $this->apiKey ? 'UUID format: ' . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $this->apiKey) ? 'Valid' : 'Invalid') : 'Not set',
            'base_url' => $this->baseUrl
        ];
    }
}
