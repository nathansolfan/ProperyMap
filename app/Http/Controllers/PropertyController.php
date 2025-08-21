<?php

namespace App\Http\Controllers;

use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $service = new PropertyService();
        $result = $service->getProperties('LONDON');

        return view('properties.index', $result);
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

        return view('property.index', $result);

    }
}
