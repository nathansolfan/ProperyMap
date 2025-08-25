<?php

namespace App\Http\Controllers;

use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    private $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function index()
    {
        return view('properties.index', [
            'properties' => [],
            'search' => 'UK Property Sales',
            'count' => 0,
            'sortBy' => 'street_number'
        ]);
    }

    // Busca por URL (GET) - CORRIGIDO para capturar o parâmetro sort
    public function search($searchTerm, Request $request)
    {
        $sortBy = $request->input('sort', 'street_number');

        $result = $this->propertyService->getProperties($searchTerm, $sortBy);
        $result['sortBy'] = $sortBy;

        return view('properties.index', $result);
    }

    // Busca por formulário (POST) - CORRIGIDO
    public function searchPost(Request $request)
    {
        $searchTerm = $request->input('search');
        $sortBy = $request->input('sort', 'street_number');

        // Verificação de debug - temporariamente
        // dd('POST searchPost:', $searchTerm, $sortBy, $request->all());

        $result = $this->propertyService->getProperties($searchTerm, $sortBy);
        $result['sortBy'] = $sortBy;

        return view('properties.index', $result);
    }

    // Busca por rua (formulário específico) - CORRIGIDO
    public function searchByStreet(Request $request)
    {
        $street = $request->input('street');
        $city = $request->input('city', 'LONDON');
        $sortBy = $request->input('sort', 'street_number');

        // Verificação de debug - temporariamente
        // dd('POST searchByStreet:', $street, $city, $sortBy, $request->all());

        $result = $this->propertyService->getPropertiesByStreet($street, $city, $sortBy);
        $result['sortBy'] = $sortBy;

        return view('properties.index', $result);
    }
}
