<?php

namespace App\Modules\Purchases\Services;

use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Purchases\Events\CompraConfirmada;
use App\Modules\Purchases\Models\Compra;
use App\Modules\Purchases\Models\CompraItem;
use App\Modules\Purchases\Models\Proveedor;
use App\Modules\Sales\Services\SaleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Compra::with(['proveedor', 'usuario', 'items']);

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'ilike', "%{$search}%")
                  ->orWhereHas('proveedor', fn($pq) => $pq->where('nombre_completo', 'ilike', "%{$search}%"));
            });
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['proveedor_id'])) {
            $query->where('proveedor_id', $filters['proveedor_id']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->where('fecha_emision', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->where('fecha_emision', '<=', $filters['fecha_hasta']);
        }

        return $query->orderByDesc('fecha_emision')
            ->paginate($filters['per_page'] ?? 25);
    }

    public function findById(string $id): ?Compra
    {
        return Compra::with(['proveedor', 'usuario', 'items.producto', 'items.unidadMedida'])
            ->find($id);
    }

    public function create(array $data): Compra
    {
        $data['usuario_id'] = $data['usuario_id'] ?? Auth::id();
        $data['origen'] = $data['origen'] ?? 'admin';

        return DB::transaction(function () use ($data) {
            $compra = new Compra();
            $compra->fill([
                'codigo' => $data['codigo'] ?? $this->generateCode(),
                'proveedor_id' => $data['proveedor_id'],
                'usuario_id' => $data['usuario_id'],
                'documento_tipo_id' => $data['documento_tipo_id'] ?? null,
                'serie' => $data['serie'] ?? null,
                'numero' => $data['numero'] ?? null,
                'fecha_emision' => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'moneda_id' => $data['moneda_id'] ?? null,
                'almacen_id' => $data['almacen_id'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'origen' => $data['origen'],
                'estado' => 'borrador',
            ]);

            $items = $data['items'] ?? [];
            $this->validateItems($items);

            $subtotal = 0;
            $igv = 0;
            $total = 0;
            foreach ($items as $itemData) {
                // Importación única: la fórmula IGV vive en SaleService
                $line = SaleService::calcularLineaIgv(
                    (float) $itemData['cantidad'],
                    (float) $itemData['precio_unitario'],
                    (float) ($itemData['descuento_linea'] ?? 0),
                );
                $subtotal += $line['gravada'];
                $igv += $line['igv'];
                $total += $line['total'];
            }

            $compra->subtotal = round($subtotal, 2);
            $compra->igv = round($igv, 2);
            $compra->total = round($total, 2);
            $compra->saldo_pendiente = $compra->total;
            $compra->save();

            foreach ($items as $index => $itemData) {
                $line = SaleService::calcularLineaIgv(
                    (float) $itemData['cantidad'],
                    (float) $itemData['precio_unitario'],
                    (float) ($itemData['descuento_linea'] ?? 0),
                );
                CompraItem::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $itemData['producto_id'],
                    'unidad_medida_id' => $itemData['unidad_medida_id'] ?? null,
                    'numero_linea' => $index + 1,
                    'cantidad' => $itemData['cantidad'],
                    'precio_unitario' => $itemData['precio_unitario'],
                    'descuento_linea' => $itemData['descuento_linea'] ?? 0,
                    'subtotal' => $line['gravada'],
                    'igv' => $line['igv'],
                    'total' => $line['total'],
                    'observaciones' => $itemData['observaciones'] ?? null,
                ]);
            }

            return $compra->fresh(['items.producto', 'proveedor', 'usuario']);
        });
    }

    public function update(Compra $compra, array $data): Compra
    {
        if (in_array($compra->estado, ['anulada', 'pagada', 'confirmada'], true)) {
            throw ValidationException::withMessages([
                'estado' => ['No se puede modificar una compra anulada, pagada o confirmada.'],
            ]);
        }

        return DB::transaction(function () use ($compra, $data) {
            if (isset($data['items'])) {
                $this->validateItems($data['items']);
                $compra->items()->delete();
                $subtotal = 0;
                $igv = 0;
                $total = 0;
                foreach ($data['items'] as $index => $itemData) {
                    $line = SaleService::calcularLineaIgv(
                        (float) $itemData['cantidad'],
                        (float) $itemData['precio_unitario'],
                        (float) ($itemData['descuento_linea'] ?? 0),
                    );
                    $subtotal += $line['gravada'];
                    $igv += $line['igv'];
                    $total += $line['total'];
                    CompraItem::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $itemData['producto_id'],
                        'unidad_medida_id' => $itemData['unidad_medida_id'] ?? null,
                        'numero_linea' => $index + 1,
                        'cantidad' => $itemData['cantidad'],
                        'precio_unitario' => $itemData['precio_unitario'],
                        'descuento_linea' => $itemData['descuento_linea'] ?? 0,
                        'subtotal' => $line['gravada'],
                        'igv' => $line['igv'],
                        'total' => $line['total'],
                        'observaciones' => $itemData['observaciones'] ?? null,
                    ]);
                }
                $data['subtotal'] = round($subtotal, 2);
                $data['igv'] = round($igv, 2);
                $data['total'] = round($total, 2);
                if (array_key_exists('total', $data) && !array_key_exists('saldo_pendiente', $data)) {
                    $data['saldo_pendiente'] = $data['total'];
                }
            }
            $compra->update($data);
            return $compra->fresh(['items.producto', 'proveedor', 'usuario']);
        });
    }

    /**
     * A-04 replicado: confirmar ejecuta el side-effect crítico (aumentar stock)
     * INLINE dentro de la misma transacción. El evento CompraConfirmada se
     * dispara solo para observabilidad post-commit.
     */
    public function confirmar(Compra $compra): Compra
    {
        if ($compra->estado === 'confirmada') {
            return $compra;
        }
        return DB::transaction(function () use ($compra) {
            $compra->update(['estado' => 'confirmada']);
            $this->aumentarStock($compra->fresh());
            event(new CompraConfirmada($compra));
            return $compra->fresh();
        });
    }

    public function anular(Compra $compra): Compra
    {
        return DB::transaction(function () use ($compra) {
            $compra->update([
                'estado' => 'anulada',
                'saldo_pendiente' => 0,
            ]);
            return $compra->fresh();
        });
    }

    public function delete(Compra $compra): void
    {
        $compra->delete();
    }

    private function aumentarStock(Compra $compra): void
    {
        $almacenId = $compra->almacen_id;
        foreach ($compra->items as $item) {
            $stock = \App\Modules\Inventory\Models\Stock::where('producto_id', $item->producto_id)
                ->where('almacen_id', $almacenId)
                ->lockForUpdate()
                ->first();

            $saldoAnterior = $stock
                ? (float) $stock->cantidad_disponible
                : (float) \App\Modules\Products\Models\Product::where('id', $item->producto_id)->value('stock_actual');
            $saldoNuevo = $saldoAnterior + (float) $item->cantidad;

            if (!$stock) {
                \App\Modules\Inventory\Models\Stock::create([
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

            \App\Modules\Products\Models\Product::where('id', $item->producto_id)
                ->update(['stock_actual' => $saldoNuevo]);

            \App\Modules\Inventory\Models\InventoryMovement::create([
                'producto_id' => $item->producto_id,
                'almacen_id' => $almacenId,
                'tipo_movimiento' => 'entrada',
                'referencia_tipo' => 'purchase',
                'referencia_id' => $compra->id,
                'cantidad' => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'valor_total' => $item->total,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoNuevo,
                'observaciones' => "Compra {$compra->codigo}",
                'usuario_id' => Auth::id() ?? $compra->usuario_id,
                'fecha_movimiento' => now(),
            ]);
        }
    }

    private function validateItems(array $items): void
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['Debe incluir al menos un ítem en la compra.'],
            ]);
        }
    }

    private function generateCode(): string
    {
        $n = (int) DB::selectOne("SELECT nextval('purchases_codigo_seq') as n")->n;
        return sprintf('C-%05d', $n);
    }
}
