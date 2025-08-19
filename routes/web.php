<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/map', [\App\Http\Controllers\PropertyController::class, 'index'])->name('property.index');

// Rotas para testar o API do HM Land Registry
Route::prefix('test')->group(function () {
    Route::get('/', [TestController::class, 'index']);
    Route::get('/connection', [TestController::class, 'testConnection']);
    Route::get('/datasets', [TestController::class, 'getDatasets']);
    Route::get('/public-api', [TestController::class, 'testPublicAPI']);
    Route::get('/property-prices', [TestController::class, 'getPropertyPrices']);
    Route::get('/check-api-key', [TestController::class, 'checkApiKey']);
    Route::get('/all', [TestController::class, 'testAll']);
});


Route::get('/liverpool-data', [TestController::class, 'getLiverpoolData']);

