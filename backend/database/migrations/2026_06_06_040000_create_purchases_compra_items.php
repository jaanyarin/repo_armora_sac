<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases_compra_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('compra_id', 26);
            $table->foreign('compra_id')->references('id')->on('purchases_compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('products');
            $table->foreignId('unidad_medida_id')->nullable()->constrained('dim_unidad_medida');
            $table->unsignedInteger('numero_linea');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento_linea', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('igv', 12, 2);
            $table->decimal('total', 12, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('compra_id');
            $table->unique(['compra_id', 'numero_linea']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases_compra_items');
    }
};
