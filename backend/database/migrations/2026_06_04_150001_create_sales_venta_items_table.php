<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_venta_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('venta_id');
            $table->foreignId('producto_id')->constrained('products');
            $table->foreignId('unidad_medida_id')->constrained('dim_unidad_medida');
            $table->unsignedInteger('numero_linea');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento_linea', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('venta_id')->references('id')->on('sales_ventas')->onDelete('cascade');
            $table->index('venta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_venta_items');
    }
};
