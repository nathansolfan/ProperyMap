<?php

namespace App\Http\Controllers;

use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
//    public function index()
//    {
//        $service = new PropertyService();
//        $result = $service->getPropertiesByStreet('', 'LONDON'); // Busca geral em Londres
//
//        return view('properties.index', $result);
//    }

    public function index()
    {
        return view('properties.index', [
            'properties' => [],
            'search' => 'UK Property Sales',
            'count' => 0
        ]);
    }

    public function search($searchTerm)
    {
        $service = new PropertyService();
        $result = $service->getProperties($searchTerm);

        return view('properties.index', $result);
    }

    public function searchPost(Request $request)
    {
        $searchTerm = $request->input('search');
        $service = new PropertyService();
        $result = $service->getProperties($searchTerm);

        return view('properties.index', $result);
    }

    public function searchByStreet(Request $request)
    {
        $street = $request->input('street');
        $city = $request->input('city', 'LONDON');

        $service = new PropertyService();
        $result = $service->getPropertiesByStreet($street, $city);

        return view('properties.index', $result);

    }
}
