<?php

namespace App\Http\Controllers;

use App\Services\TestApiService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $testService;

    public function __construct(TestApiService $testService)
    {
        $this->testService = $testService;
    }

    /**
     * Página principal de testes
     */
    public function index()
    {
        return view('test.index');
    }

    /**
     * Testar conexão básica
     */
    public function testConnection()
    {
        $result = $this->testService->testConnection();
        return response()->json($result);
    }

    /**
     * Listar datasets disponíveis
     */
    public function getDatasets()
    {
        $result = $this->testService->getAvailableDatasets();
        return response()->json($result);
    }

    /**
     * Testar todos os endpoints
     */
    public function testAll()
    {
        $results = $this->testService->testAllEndpoints();
        return response()->json($results);
    }

    /**
     * Verificar status do API key
     */
    public function checkApiKey()
    {
        $result = $this->testService->checkApiKeyStatus();
        return response()->json($result);
    }

    /**
     * Testar API pública de preços
     */
    public function testPublicAPI(Request $request)
    {
        $postcode = $request->get('postcode', 'L1');
        $result = $this->testService->testPublicPriceAPI($postcode);
        return response()->json($result);
    }

    /**
     * Buscar preços por postcode
     */
    public function getPropertyPrices(Request $request)
    {
        $postcode = $request->get('postcode', 'L1');
        $result = $this->testService->getPropertyPrices($postcode);
        return response()->json($result);
    }

    /**
     * Buscar dados reais de Liverpool
     */
    public function getLiverpoolData()
    {
        $result = $this->testService->getLiverpoolData();
        return response()->json($result);
    }
}
