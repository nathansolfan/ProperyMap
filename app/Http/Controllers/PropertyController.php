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

    public function index()
    {
        $property = $this->propertyService->getPriceByYear();

        return view('')

    }
}
