<?php

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Models\CreditNote;
use App\Modules\Sales\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreditNoteService
{
    public function emitirPorVenta(Sale $sale, string $motivo, ?int $tipoId = null): CreditNote
    {
        return DB::transaction(function () use ($sale, $motivo, $tipoId) {
            $creditNote = CreditNote::create([
                'codigo' => $this->generateCode(),
                'venta_id' => $sale->id,
                'nota_credito_tipo_id' => $tipoId,
                'serie' => 'NC' . substr($sale->serie ?? '001', 0, 3),
                'numero' => (string) (CreditNote::withTrashed()->count() + 1),
                'fecha_emision' => now(),
                'motivo' => $motivo,
                'subtotal' => $sale->subtotal,
                'igv' => $sale->igv,
                'total' => $sale->total,
                'estado' => 'emitida',
                'usuario_id' => Auth::id() ?? $sale->usuario_id,
            ]);

            app(\App\Modules\Inventory\Services\InventoryService::class)
                ->reingresarPorVenta($sale);

            return $creditNote;
        });
    }

    private function generateCode(): string
    {
        $prefix = 'NC';
        $last = CreditNote::withTrashed()->count() + 1;
        return $prefix . '-' . date('Y') . '-' . Str::padLeft($last, 5, '0');
    }
}
