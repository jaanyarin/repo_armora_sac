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
    public function descontarPorVenta(Sale $sale, ?int $almacenId = null): void
    {
        $this->aplicarMovimientoVenta($sale, signo: -1, tipo: 'salida', refTipo: 'sale', obsPrefix: 'Venta', almacenId: $almacenId);
    }

    public function reingresarPorVenta(Sale $sale, ?int $almacenId = null): void
    {
        $this->aplicarMovimientoVenta($sale, signo: +1, tipo: 'entrada', refTipo: 'sale_anulacion', obsPrefix: 'Anulación venta', almacenId: $almacenId);
    }

    /**
     * A-02: resolver almacén destino. Si no se pasa, se usa:
     *  1) El almacén `principal` activo
     *  2) El primer almacén activo
     *  3) Crea uno por defecto si la tabla está vacía (idempotente vía firstOrCreate)
     */
    private function resolverAlmacenId(?int $explicit): int
    {
        if ($explicit !== null) {
            return $explicit;
        }
        $principal = DB::table('dim_almacen')->where('principal', true)->where('activo', true)->value('id');
        if ($principal) {
            return (int) $principal;
        }
        $primero = DB::table('dim_almacen')->where('activo', true)->orderBy('id')->value('id');
        if ($primero) {
            return (int) $primero;
        }
        return (int) DB::table('dim_almacen')->insertGetId([
            'codigo' => 'ALM-001',
            'nombre' => 'Almacén Principal',
            'principal' => true,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function aplicarMovimientoVenta(Sale $sale, int $signo, string $tipo, string $refTipo, string $obsPrefix, ?int $almacenId = null): void
    {
        $callback = function () use ($sale, $signo, $tipo, $refTipo, $obsPrefix, $almacenId) {
            $almacenId = $this->resolverAlmacenId($almacenId);

            foreach ($sale->items()->with('producto')->get() as $item) {
                $stock = Stock::where('producto_id', $item->producto_id)
                    ->where('almacen_id', $almacenId)
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
                        'almacen_id' => $almacenId,
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
                    'almacen_id' => $almacenId,
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
