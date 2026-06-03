<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('codigo_sunat', 10)->nullable();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->foreignId('unidad_medida_id')->constrained('dim_unidad_medida');
            $table->foreignId('producto_clase_id')->nullable()->constrained('dim_producto_clase');
            $table->foreignId('producto_subclase_id')->nullable()->constrained('dim_producto_subclase');
            $table->foreignId('familia_sunat_id')->nullable()->constrained('dim_familia_sunat');
            $table->foreignId('clase_sunat_id')->nullable()->constrained('dim_clase_sunat');
            $table->foreignId('tipo_afeccion_igv_id')->nullable()->constrained('dim_tipo_afeccion_igv');
            $table->foreignId('tipo_calculo_isc_id')->nullable()->constrained('dim_tipo_calculo_isc');
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->decimal('precio_venta_usd', 12, 2)->default(0);
            $table->decimal('costo_promedio', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
