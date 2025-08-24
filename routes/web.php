<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{search}', [PropertyController::class, 'search']);
Route::post('/properties/search', [PropertyController::class, 'searchPost']);
Route::post('/properties/search-street', [PropertyController::class, 'searchByStreet'] );
