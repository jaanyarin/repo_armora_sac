<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Catalog\Http\Controllers\CatalogController;
use App\Modules\Customers\Http\Controllers\CustomerController;
use App\Modules\Inventory\Http\Controllers\InventoryController;
use App\Modules\Products\Http\Controllers\ProductController;
use App\Modules\Sales\Http\Controllers\SaleController;
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

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->middleware('permission:ver-clientes');
        Route::get('{customer}', [CustomerController::class, 'show'])->middleware('permission:ver-clientes');
        Route::post('/', [CustomerController::class, 'store'])->middleware('permission:crear-clientes');
        Route::put('{customer}', [CustomerController::class, 'update'])->middleware('permission:editar-clientes');
        Route::delete('{customer}', [CustomerController::class, 'destroy'])->middleware('permission:eliminar-clientes');
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->middleware('permission:ver-productos');
        Route::get('{product}', [ProductController::class, 'show'])->middleware('permission:ver-productos');
        Route::post('/', [ProductController::class, 'store'])->middleware('permission:crear-productos');
        Route::put('{product}', [ProductController::class, 'update'])->middleware('permission:editar-productos');
        Route::delete('{product}', [ProductController::class, 'destroy'])->middleware('permission:eliminar-productos');
    });

    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->middleware('permission:ver-ventas');
        Route::get('{sale}', [SaleController::class, 'show'])->middleware('permission:ver-ventas');
        Route::post('/', [SaleController::class, 'store'])->middleware('permission:crear-ventas');
        Route::put('{sale}', [SaleController::class, 'update'])->middleware('permission:ver-ventas');
        Route::post('{sale}/confirmar', [SaleController::class, 'confirmar'])->middleware('permission:crear-ventas');
        Route::post('{sale}/anular', [SaleController::class, 'anular'])->middleware('permission:anular-ventas');
        Route::delete('{sale}', [SaleController::class, 'destroy'])->middleware('permission:ver-ventas');
        Route::post('{sale}/nota-credito', [SaleController::class, 'emitirNotaCredito'])->middleware('permission:nota-credito');
    });

    Route::prefix('inventory')->group(function () {
        Route::get('stock', [InventoryController::class, 'stock'])->middleware('permission:ver-stock');
        Route::get('stock/{productId}', [InventoryController::class, 'stockByProduct'])->middleware('permission:ver-stock');
        Route::get('kardex', [InventoryController::class, 'kardex'])->middleware('permission:kardex');
    });
});
