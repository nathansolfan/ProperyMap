<?php

namespace App\Http\Controllers;

use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{

    protected $propertyService;
    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function index(Request $request)
    {
        $postcodeInput = $request->input('postcode', 'L1');
        $data = $this->propertyService->getPriceByYear($postcodeInput);

// Descompacta para variáveis individuais
        $transactions = $data['transactions'] ?? [];
        $lat = $data['lat'] ?? 53.4084;
        $lng = $data['lng'] ?? -2.9916;
        $postcode = $data['postcode'] ?? $postcodeInput;

        return view('property.map', compact('transactions', 'lat', 'lng', 'postcode'));

    }



}
