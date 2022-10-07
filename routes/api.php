<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\CategoriaController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(ProductoController::class)->group(function () {
    Route::get('/productos', 'index');
    Route::post('/producto', 'store');
    Route::get('/producto/{id}', 'show');
    Route::post('/producto/{id}', 'update');
    Route::delete('/producto/{id}', 'destroy');
});

Route::controller(MarcaController::class)->group(function () {
    Route::get('/marcas', 'index');
    Route::post('/marca', 'store');
    Route::get('/marca/{id}', 'show');
    Route::put('/marca/{id}', 'update');
    Route::delete('/marca/{id}', 'destroy');
});

Route::controller(CategoriaController::class)->group(function () {
    Route::get('/categorias', 'index');
    Route::post('/categoria', 'store');
    Route::get('/categoria/{id}', 'show');
    Route::put('/categoria/{id}', 'update');
    Route::delete('/categoria/{id}', 'destroy');
});