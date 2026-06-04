<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_notas_credito', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('codigo', 20)->unique();
            $table->ulid('venta_id');
            $table->foreignId('nota_credito_tipo_id')->nullable()->constrained('dim_nota_credito_tipo');
            $table->string('serie', 10)->nullable();
            $table->string('numero', 20)->nullable();
            $table->date('fecha_emision');
            $table->text('motivo');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['emitida', 'anulada'])->default('emitida');
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('venta_id')->references('id')->on('sales_ventas');
            $table->index('venta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_notas_credito');
    }
};
