<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_ventas', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('codigo', 20)->unique();
            $table->foreignId('cliente_id')->constrained('customers');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('documento_tipo_id')->nullable()->constrained('dim_documento_tipo');
            $table->string('serie', 10)->nullable();
            $table->string('numero', 20)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('moneda_id')->nullable()->constrained('dim_moneda');
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
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_ventas');
    }
};
