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

    // Busca por URL (GET) - ATUALIZADO
    public function search($searchTerm, Request $request)
    {
        // Garante que o parâmetro sort seja capturado corretamente
        $sortBy = $request->input('sort', 'street_number');

        $result = $this->propertyService->getProperties($searchTerm, $sortBy);

        // Garante que o parâmetro sort esteja disponível na view
        $result['sortBy'] = $sortBy;

        return view('properties.index', $result);
    }

    // Busca por formulário (POST) - ATUALIZADO
    public function searchPost(Request $request)
    {
        $searchTerm = $request->input('search');
        $sortBy = $request->input('sort', 'street_number');

        $result = $this->propertyService->getProperties($searchTerm, $sortBy);

        // Garante que o parâmetro sort esteja disponível na view
        $result['sortBy'] = $sortBy;

        return view('properties.index', $result);
    }

    // Busca por rua (formulário específico)
    public function searchByStreet(Request $request)
    {
        $street = $request->input('street');
        $city = $request->input('city', 'LONDON');
        $sortBy = $request->input('sort', 'street_number');

        $result = $this->propertyService->getPropertiesByStreet($street, $city, $sortBy);

        // Garante que o parâmetro sort esteja disponível na view
        $result['sortBy'] = $sortBy;

        return view('properties.index', $result);
    }
}
