<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            -- ============================================================
            -- HITO-AUDIT-001 / C-03: Recalcular IGV con fórmula única
            -- Convención: subtotal = total / 1.18, igv = subtotal * 0.18
            -- Referencia: _auditoria/ADRs_AUDITORIA/ADR-A001-convencion-igv-unico.md
            -- ============================================================

            -- 1) Ítems de venta: recalcular subtotal e igv a partir de total
            -- (sales_venta_items NO usa SoftDeletes, por eso no se filtra por deleted_at)
            UPDATE sales_venta_items
            SET
                subtotal = ROUND(total / 1.18, 2),
                igv      = ROUND(ROUND(total / 1.18, 2) * 0.18, 2)
            WHERE total > 0;

            -- 2) Cabecera de venta: recalcular a partir de la suma de ítems
            UPDATE sales_ventas sv
            SET
                subtotal = COALESCE(items.sum_subtotal, sv.subtotal),
                igv      = COALESCE(items.sum_igv, sv.igv),
                total    = COALESCE(items.sum_total, sv.total)
            FROM (
                SELECT
                    venta_id,
                    ROUND(SUM(subtotal), 2) AS sum_subtotal,
                    ROUND(SUM(igv), 2)      AS sum_igv,
                    ROUND(SUM(total), 2)    AS sum_total
                FROM sales_venta_items
                GROUP BY venta_id
            ) AS items
            WHERE sv.id = items.venta_id
              AND sv.deleted_at IS NULL;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('-- Migration is one-way corrective (recalculation). No down() reversal.');
    }
};
