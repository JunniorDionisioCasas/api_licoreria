<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DescuentoController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(ProductoController::class)->group(function () {
    Route::get('/productos', 'index');
    Route::post('/producto', 'store');
    Route::get('/producto/{id}', 'show');
    Route::post('/producto/{id}', 'update');
    Route::delete('/producto/{id}', 'destroy');
    Route::get('/count_productos', 'info_filtro');
    Route::get('/producto/buscar/{searchParams}', 'search_by_filtro');
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

Route::controller(PedidoController::class)->group(function () {
    Route::get('/pedidos', 'index');
    Route::post('/pedido', 'store');
    Route::get('/pedido/{id}', 'show');
    Route::put('/pedido/{id}', 'update');
    Route::delete('/pedido/{id}', 'destroy');
});

Route::controller(DescuentoController::class)->group(function () {
    Route::get('/descuentos', 'index');
    Route::post('/descuento', 'store');
    Route::get('/descuento/{id}', 'show');
    Route::put('/descuento/{id}', 'update');
    Route::delete('/descuento/{id}', 'destroy');
    Route::get('/descuento/buscar/{searchParams}', 'search_by_code');
});