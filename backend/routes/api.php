<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Catalog\Http\Controllers\CatalogController;
use App\Modules\Customers\Http\Controllers\CustomerController;
use App\Modules\Products\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::controller(CatalogController::class)->prefix('catalog')->group(function () {
        Route::get('monedas', 'monedas');
        Route::get('unidades-medida', 'unidadesMedida');
        Route::get('paises', 'paises');
        Route::get('documentos', 'documentos');
        Route::get('departamentos', 'departamentos');
        Route::get('provincias/{departamentoId?}', 'provincias');
        Route::get('ubigeos/{provinciaId?}', 'ubigeos');
        Route::get('tipos-afeccion-igv', 'tipoAfeccionIgv');
        Route::get('tipos-calculo-isc', 'tipoCalculoIsc');
        Route::get('nota-credito-tipos', 'notaCreditoTipos');
        Route::get('segmentos', 'segmentos');
        Route::get('tipos-cliente', 'tiposCliente');
        Route::get('familias', 'familias');
        Route::get('clases', 'clases');
        Route::get('producto-clases', 'productoClases');
        Route::get('producto-subclases/{claseId?}', 'productoSubclases');
        Route::get('tipos-venta', 'tiposVenta');
        Route::get('tipos-compra', 'tiposCompra');
        Route::get('documento-tipos', 'documentoTipos');
        Route::get('tipo-cambio', 'tipoCambio');
        Route::get('lista-precios', 'listaPrecios');
        Route::get('estados-civil', 'estadoCivil');
        Route::get('sexos', 'sexos');
        Route::get('roles', 'roles');
        Route::get('permisos', 'permisos');
    });

    Route::apiResource('customers', CustomerController::class)->parameters(['customers' => 'customer']);
    Route::apiResource('products', ProductController::class)->parameters(['products' => 'product']);
});
