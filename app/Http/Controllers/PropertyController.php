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

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $street = $request->input('street', '');
        $city = $request->input('city', 'LONDON');
        $sortBy = $request->input('sort', 'street_number');

        // Se não tem busca, mostra página inicial
        if (empty($search) && empty($street)) {
            return view('properties.index', [
                'properties' => [],
                'search' => 'UK Property Sales',
                'count' => 0,
                'sortBy' => $sortBy
            ]);
        }

        // Se tem street, usa busca por rua
        if (!empty($street)) {
            $result = $this->propertyService->getPropertiesByStreet($street, $city, $sortBy);
        } else {
            // Senão, usa busca geral
            $result = $this->propertyService->getProperties($search, $sortBy);
        }

        $result['sortBy'] = $sortBy;
        return view('properties.index', $result);
    }
}
