<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Models\Stock;
use App\Modules\Products\Models\Product;
use App\Modules\Sales\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function descontarPorVenta(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items()->with('producto')->get() as $item) {
                $stock = Stock::where('producto_id', $item->producto_id)
                    ->whereNull('almacen_id')
                    ->lockForUpdate()
                    ->first();

                $product = Product::find($item->producto_id);
                $saldoAnterior = $stock
                    ? (float) $stock->cantidad_disponible
                    : (float) $product->stock_actual;
                $saldoNuevo = max(0, $saldoAnterior - (float) $item->cantidad);

                if (!$stock) {
                    $stock = Stock::create([
                        'producto_id' => $item->producto_id,
                        'almacen_id' => null,
                        'cantidad_disponible' => $saldoNuevo,
                        'ultima_actualizacion' => now(),
                    ]);
                } else {
                    $stock->update([
                        'cantidad_disponible' => $saldoNuevo,
                        'ultima_actualizacion' => now(),
                    ]);
                }

                Product::where('id', $item->producto_id)
                    ->update(['stock_actual' => $saldoNuevo]);

                InventoryMovement::create([
                    'producto_id' => $item->producto_id,
                    'almacen_id' => null,
                    'tipo_movimiento' => 'salida',
                    'referencia_tipo' => 'sale',
                    'referencia_id' => $sale->id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'valor_total' => $item->total,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $saldoNuevo,
                    'observaciones' => "Venta {$sale->codigo}",
                    'usuario_id' => Auth::id() ?? $sale->usuario_id,
                    'fecha_movimiento' => now(),
                ]);
            }
        });
    }

    public function reingresarPorVenta(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items()->with('producto')->get() as $item) {
                $stock = Stock::where('producto_id', $item->producto_id)
                    ->whereNull('almacen_id')
                    ->lockForUpdate()
                    ->first();

                $product = Product::find($item->producto_id);
                $saldoAnterior = $stock
                    ? (float) $stock->cantidad_disponible
                    : (float) $product->stock_actual;
                $saldoNuevo = $saldoAnterior + (float) $item->cantidad;

                if ($stock) {
                    $stock->update([
                        'cantidad_disponible' => $saldoNuevo,
                        'ultima_actualizacion' => now(),
                    ]);
                } else {
                    $stock = Stock::create([
                        'producto_id' => $item->producto_id,
                        'almacen_id' => null,
                        'cantidad_disponible' => $saldoNuevo,
                        'ultima_actualizacion' => now(),
                    ]);
                }

                Product::where('id', $item->producto_id)
                    ->update(['stock_actual' => $saldoNuevo]);

                InventoryMovement::create([
                    'producto_id' => $item->producto_id,
                    'almacen_id' => null,
                    'tipo_movimiento' => 'entrada',
                    'referencia_tipo' => 'sale_anulacion',
                    'referencia_id' => $sale->id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'valor_total' => $item->total,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $saldoNuevo,
                    'observaciones' => "Anulación venta {$sale->codigo}",
                    'usuario_id' => Auth::id() ?? $sale->usuario_id,
                    'fecha_movimiento' => now(),
                ]);
            }
        });
    }
}
