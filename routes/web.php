<?php

use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/map', [\App\Http\Controllers\PropertyController::class, 'index']);
