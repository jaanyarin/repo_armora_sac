<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases_compras', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('codigo', 20)->unique();
            $table->string('proveedor_id', 26);
            $table->foreign('proveedor_id')->references('id')->on('purchases_proveedores');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('documento_tipo_id')->nullable()->constrained('dim_documento_tipo');
            $table->string('serie', 10)->nullable();
            $table->string('numero', 20)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('moneda_id')->nullable()->constrained('dim_moneda');
            $table->foreignId('almacen_id')->nullable()->constrained('dim_almacen');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento_global', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('isc', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('saldo_pendiente', 12, 2)->default(0);
            $table->enum('estado', ['borrador', 'confirmada', 'anulada', 'pagada', 'parcial'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->string('origen', 20)->default('admin');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha_emision']);
            $table->index('proveedor_id');
        });

        // A-01 replicado: secuencia PostgreSQL para codigo atómico
        DB::statement("
            CREATE SEQUENCE IF NOT EXISTS purchases_codigo_seq
            START WITH " . (int) (DB::table('purchases_compras')
                ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(codigo FROM '[0-9]+$') AS BIGINT)), 0) + 1")
                ->value('max') ?? 1) . "
            INCREMENT BY 1
            NO MINVALUE
            NO MAXVALUE
            CACHE 1
        ");
    }

    public function down(): void
    {
        DB::statement('DROP SEQUENCE IF EXISTS purchases_codigo_seq');
        Schema::dropIfExists('purchases_compras');
    }
};
