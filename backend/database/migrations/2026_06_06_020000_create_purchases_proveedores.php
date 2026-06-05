<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases_proveedores', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('codigo', 20)->unique();
            $table->foreignId('tipo_documento_id')->nullable()->constrained('dim_documento_tipo');
            $table->string('numero_documento', 20);
            $table->string('nombre_completo', 200);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contacto_nombre', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo_documento_id', 'numero_documento']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases_proveedores');
    }
};
