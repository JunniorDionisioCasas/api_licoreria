<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DescuentoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TipoPedidoController;
use App\Http\Controllers\TipoDescuentoController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\CargosController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\DistritoController;

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
    Route::put('/producto_cont/{id}', 'count_vistas');
    Route::get('/data_homepage', 'home_data');
});

Route::controller(CategoriaController::class)->group(function () {
    Route::get('/categorias', 'index');
    Route::post('/categoria', 'store');
    Route::get('/categoria/{id}', 'show');
    Route::put('/categoria/{id}', 'update');
    Route::delete('/categoria/{id}', 'destroy');
});

Route::controller(MarcaController::class)->group(function () {
    Route::get('/marcas', 'index');
    Route::post('/marca', 'store');
    Route::get('/marca/{id}', 'show');
    Route::put('/marca/{id}', 'update');
    Route::delete('/marca/{id}', 'destroy');
});

Route::controller(ProveedorController::class)->group(function () {
    Route::get('/proveedores', 'index');
    Route::post('/proveedor', 'store');
    Route::get('/proveedor/{id}', 'show');
    Route::put('/proveedor/{id}', 'update');
    Route::delete('/proveedor/{id}', 'destroy');
});

Route::controller(PedidoController::class)->group(function () {
    Route::get('/pedidos', 'index');
    Route::post('/pedido', 'store');
    Route::get('/pedido/{id}', 'show');
    Route::put('/pedido/{id}', 'update');
    Route::delete('/pedido/{id}', 'destroy');
    Route::put('/pedido_pagado/{id}', 'pedido_pagado');
});

Route::controller(TipoPedidoController::class)->group(function () {
    Route::get('/tipo_pedidos', 'index');
    Route::post('/tipo_pedido', 'store');
    Route::get('/tipo_pedido/{id}', 'show');
    Route::put('/tipo_pedido/{id}', 'update');
    Route::delete('/tipo_pedido/{id}', 'destroy');
});

Route::controller(DescuentoController::class)->group(function () {
    Route::get('/descuentos', 'index');
    Route::get('/all_descuentos', 'crud_index');
    Route::post('/descuento', 'store');
    Route::get('/descuento/{id}', 'show');
    Route::put('/descuento/{id}', 'update');
    Route::delete('/descuento/{id}', 'destroy');
    Route::get('/descuento/buscar/{searchParams}', 'search_by_code');
    Route::get('/descuento/check1stbuy/{id}', 'check_1st_buy');
});

Route::controller(TipoDescuentoController::class)->group(function () {
    Route::get('/tipo_descuentos', 'index');
});

Route::controller(CargosController::class)->group(function () {
    Route::get('/cargos', 'index');
    Route::post('/cargo', 'store');
    Route::get('/cargo/{id}', 'show');
    Route::put('/cargo/{id}', 'update');
    Route::delete('/cargo/{id}', 'destroy');
});

Route::controller(ProvinciaController::class)->group(function () {
    Route::get('/provincias', 'index');
});

Route::controller(DistritoController::class)->group(function () {
    Route::get('/distritos_by_provincia/{idProvincia}', 'list_by_provincia');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/empleados', 'index_empleados');
    Route::post('/empleado', 'store_empleado');
    Route::get('/empleado/{id}', 'show_empleado');
    Route::post('/empleado/{id}', 'update_empleado');
    Route::delete('/empleado/{id}', 'destroy_empleado');
});

Route::controller(ReportesController::class)->group(function () {
    Route::get('/reporte_ventas/{dateFrom}/{dateUntil}/{idProducto}/{idTipoPedido}/{idCliente}', 'reporte_ventas');
});

Route::controller(UserController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::group(['middleware' => ["auth:sanctum"]], function(){
    Route::controller(UserController::class)->group(function () {
        Route::get('/user-profile', 'userProfile');
        Route::get('/logout', 'logout');
        
        Route::get('/clientes', 'index_clientes');
        Route::post('/cliente/{id}', 'update_cliente');
        Route::delete('/cliente/{id}', 'destroy_cliente');
    });
});