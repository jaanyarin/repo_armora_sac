<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movimientos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('producto_id')->constrained('products');
            $table->foreignId('almacen_id')->nullable()->constrained('dim_almacen');
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->string('referencia_tipo', 50)->nullable();
            $table->string('referencia_id', 50)->nullable();
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('saldo_nuevo', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->timestamps();

            $table->index(['producto_id', 'fecha_movimiento']);
            $table->index('referencia_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movimientos');
    }
};
