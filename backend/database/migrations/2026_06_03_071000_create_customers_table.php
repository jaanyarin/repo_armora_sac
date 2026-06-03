<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('tipo_documento', 2)->default('DNI');
            $table->string('numero_documento', 15)->unique();
            $table->string('nombre_completo', 255);
            $table->string('nombre_comercial', 255)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->foreignId('ubigeo_id')->nullable()->constrained('dim_ubigeo');
            $table->string('email', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->foreignId('tipo_cliente_id')->nullable()->constrained('dim_tipo_cliente');
            $table->foreignId('segmento_id')->nullable()->constrained('dim_segmento_sunat');
            $table->foreignId('lista_precio_id')->nullable()->constrained('dim_lista_precios');
            $table->decimal('limite_credito', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
