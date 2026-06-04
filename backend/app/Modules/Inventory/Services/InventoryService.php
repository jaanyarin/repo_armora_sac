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
        $this->aplicarMovimientoVenta($sale, signo: -1, tipo: 'salida', refTipo: 'sale', obsPrefix: 'Venta');
    }

    public function reingresarPorVenta(Sale $sale): void
    {
        $this->aplicarMovimientoVenta($sale, signo: +1, tipo: 'entrada', refTipo: 'sale_anulacion', obsPrefix: 'Anulación venta');
    }

    private function aplicarMovimientoVenta(Sale $sale, int $signo, string $tipo, string $refTipo, string $obsPrefix): void
    {
        $callback = function () use ($sale, $signo, $tipo, $refTipo, $obsPrefix) {
            foreach ($sale->items()->with('producto')->get() as $item) {
                $stock = Stock::where('producto_id', $item->producto_id)
                    ->whereNull('almacen_id')
                    ->lockForUpdate()
                    ->first();

                $product = Product::find($item->producto_id);
                $saldoAnterior = $stock
                    ? (float) $stock->cantidad_disponible
                    : (float) $product->stock_actual;
                $saldoNuevo = $signo < 0
                    ? max(0, $saldoAnterior - (float) $item->cantidad)
                    : $saldoAnterior + (float) $item->cantidad;

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
                    'tipo_movimiento' => $tipo,
                    'referencia_tipo' => $refTipo,
                    'referencia_id' => $sale->id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'valor_total' => $item->total,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $saldoNuevo,
                    'observaciones' => "{$obsPrefix} {$sale->codigo}",
                    'usuario_id' => Auth::id() ?? $sale->usuario_id,
                    'fecha_movimiento' => now(),
                ]);
            }
        };

        if (DB::transactionLevel() > 0) {
            $callback();
        } else {
            DB::transaction($callback);
        }
    }
}
