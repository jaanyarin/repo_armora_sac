<?php

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Events\SaleConfirmed;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Models\SaleItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['cliente', 'usuario', 'items'])
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('codigo', 'ilike', "%{$s}%")
                  ->orWhere('serie', 'ilike', "%{$s}%")
                  ->orWhere('numero', 'ilike', "%{$s}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('nombre_completo', 'ilike', "%{$s}%")
                                                          ->orWhere('numero_documento', 'ilike', "%{$s}%"));
            }))
            ->when($filters['estado'] ?? null, fn($q, $e) => $q->where('estado', $e))
            ->when($filters['cliente_id'] ?? null, fn($q, $c) => $q->where('cliente_id', $c))
            ->when($filters['fecha_desde'] ?? null, fn($q, $f) => $q->where('fecha_emision', '>=', $f))
            ->when($filters['fecha_hasta'] ?? null, fn($q, $f) => $q->where('fecha_emision', '<=', $f))
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(string $id): ?Sale
    {
        return Sale::with([
            'cliente', 'usuario', 'items.producto.unidadMedida', 'notasCredito',
        ])->find($id);
    }

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $data['codigo'] = $data['codigo'] ?? $this->generateCode();
            $data['usuario_id'] = $data['usuario_id'] ?? Auth::id();
            $data['estado'] = $data['estado'] ?? 'borrador';

            [$subtotal, $igv, $total] = $this->calcularTotales($data['items'] ?? []);

            $data['subtotal'] = $subtotal;
            $data['igv'] = $igv;
            $data['total'] = $total;
            $data['saldo_pendiente'] = $data['estado'] === 'pagada' ? 0 : $total;

            $sale = Sale::create($data);

            foreach ($data['items'] ?? [] as $index => $item) {
                $lineTotals = $this->calcularItem($item);

                SaleItem::create([
                    'venta_id' => $sale->id,
                    'producto_id' => $item['producto_id'],
                    'unidad_medida_id' => $item['unidad_medida_id'],
                    'numero_linea' => $index + 1,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento_linea' => $item['descuento_linea'] ?? 0,
                    'subtotal' => $lineTotals['subtotal'],
                    'igv' => $lineTotals['igv'],
                    'total' => $lineTotals['total'],
                    'observaciones' => $item['observaciones'] ?? null,
                ]);
            }

            if ($sale->estado === 'confirmada') {
                event(new SaleConfirmed($sale));
            }

            return $sale->fresh(['items.producto.unidadMedida', 'cliente', 'usuario']);
        });
    }

    public function update(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            if (in_array($sale->estado, ['anulada', 'pagada'], true)) {
                throw new \DomainException('No se puede modificar una venta anulada o pagada completamente.');
            }

            if (isset($data['items'])) {
                $sale->items()->delete();
                [$subtotal, $igv, $total] = $this->calcularTotales($data['items']);

                $data['subtotal'] = $subtotal;
                $data['igv'] = $igv;
                $data['total'] = $total;

                foreach ($data['items'] as $index => $item) {
                    $lineTotals = $this->calcularItem($item);

                    SaleItem::create([
                        'venta_id' => $sale->id,
                        'producto_id' => $item['producto_id'],
                        'unidad_medida_id' => $item['unidad_medida_id'],
                        'numero_linea' => $index + 1,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'descuento_linea' => $item['descuento_linea'] ?? 0,
                        'subtotal' => $lineTotals['subtotal'],
                        'igv' => $lineTotals['igv'],
                        'total' => $lineTotals['total'],
                        'observaciones' => $item['observaciones'] ?? null,
                    ]);
                }
            }

            $previousEstado = $sale->estado;
            $sale->update($data);
            $sale->refresh();

            if ($previousEstado !== 'confirmada' && $sale->estado === 'confirmada') {
                event(new SaleConfirmed($sale));
            }

            return $sale->fresh(['items.producto.unidadMedida', 'cliente', 'usuario']);
        });
    }

    public function confirmar(Sale $sale): Sale
    {
        if ($sale->estado === 'confirmada') {
            return $sale;
        }

        return DB::transaction(function () use ($sale) {
            $sale->update(['estado' => 'confirmada']);
            event(new SaleConfirmed($sale));
            return $sale->fresh();
        });
    }

    public function anular(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            if ($sale->estado === 'anulada') {
                return $sale;
            }

            if ($sale->estado === 'confirmada' || $sale->estado === 'pagada' || $sale->estado === 'parcial') {
                app(\App\Modules\Inventory\Services\InventoryService::class)
                    ->reingresarPorVenta($sale);
            }

            $sale->update(['estado' => 'anulada']);
            return $sale->fresh();
        });
    }

    public function delete(Sale $sale): void
    {
        $sale->delete();
    }

    public static function calcularLineaIgv(float $cantidad, float $precioUnitario, float $descuentoLinea = 0.0): array
    {
        $totalLinea = round($cantidad * $precioUnitario - $descuentoLinea, 2);
        $gravada    = round($totalLinea / 1.18, 2);
        $igv        = round($gravada * 0.18, 2);

        return [
            'total_linea' => $totalLinea,
            'gravada'     => $gravada,
            'igv'         => $igv,
            'total'       => round($gravada + $igv, 2),
        ];
    }

    private function calcularItem(array $item): array
    {
        $descuento = (float) ($item['descuento_linea'] ?? 0);
        $line = self::calcularLineaIgv(
            (float) $item['cantidad'],
            (float) $item['precio_unitario'],
            $descuento,
        );
        return [
            'subtotal' => $line['gravada'],
            'igv' => $line['igv'],
            'total' => $line['total_linea'],
        ];
    }

    private function calcularTotales(array $items): array
    {
        $subtotalBase = 0.0;
        $igvTotal = 0.0;
        $total = 0.0;
        foreach ($items as $item) {
            $line = $this->calcularItem($item);
            $subtotalBase += $line['subtotal'];
            $igvTotal += $line['igv'];
            $total += $line['total'];
        }
        return [round($subtotalBase, 2), round($igvTotal, 2), round($total, 2)];
    }

    private function generateCode(): string
    {
        $prefix = 'V';
        $last = Sale::withTrashed()->count() + 1;
        return $prefix . '-' . date('Y') . '-' . Str::padLeft($last, 5, '0');
    }
}
