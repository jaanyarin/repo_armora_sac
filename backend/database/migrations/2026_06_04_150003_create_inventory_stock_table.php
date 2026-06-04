<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('products');
            $table->foreignId('almacen_id')->nullable()->constrained('dim_almacen');
            $table->decimal('cantidad_disponible', 12, 2)->default(0);
            $table->decimal('cantidad_comprometida', 12, 2)->default(0);
            $table->decimal('cantidad_minima', 12, 2)->default(0);
            $table->decimal('cantidad_maxima', 12, 2)->default(0);
            $table->timestamp('ultima_actualizacion')->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id']);
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock');
    }
};
