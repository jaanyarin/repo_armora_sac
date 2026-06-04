<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            -- ============================================================
            -- HITO-INTERCALAR-001 / A-01: Secuencia atómica para sales.codigo
            -- Referencia: _auditoria/HITO-003/HITO-003-hallazgos.md (A-01)
            -- ADR-009 Fase 0 — remediar race condition en generateCode()
            -- ============================================================

            CREATE SEQUENCE IF NOT EXISTS sales_codigo_seq
                INCREMENT BY 1
                START WITH 1
                MINVALUE 1
                NO MAXVALUE
                CACHE 1;

            -- Inicializa la secuencia al valor actual de la tabla (sobrevive a restores)
            SELECT setval(
                'sales_codigo_seq',
                GREATEST(
                    COALESCE((SELECT MAX(SUBSTRING(codigo FROM '[0-9]+$')::BIGINT) FROM sales_ventas), 0),
                    1
                )
            );
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP SEQUENCE IF EXISTS sales_codigo_seq');
    }
};
