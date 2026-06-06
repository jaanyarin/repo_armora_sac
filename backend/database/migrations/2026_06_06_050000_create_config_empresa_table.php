<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 255)->nullable();
            $table->string('nombre_comercial', 255)->nullable();
            $table->string('ruc', 11)->nullable();
            $table->string('email', 255)->nullable();
            $table->foreignId('pais_id')->nullable()->constrained('dim_pais');
            $table->string('telefono_fijo', 20)->nullable();
            $table->string('telefono_celular', 20)->nullable();
            $table->foreignId('departamento_id')->nullable()->constrained('dim_departamento');
            $table->foreignId('provincia_id')->nullable()->constrained('dim_provincia');
            $table->foreignId('ubigeo_id')->nullable()->constrained('dim_ubigeo');
            $table->string('direccion', 255)->nullable();
            $table->string('referencia', 255)->nullable();

            $table->decimal('porcentaje_igv', 5, 2)->default(18.00);
            $table->decimal('boleta_monto_dni', 12, 2)->nullable();
            $table->boolean('envio_auto_sunat')->default(false);
            $table->boolean('consolidado_requerimientos')->default(false);
            $table->boolean('consolidado_liquidaciones')->default(false);
            $table->boolean('resumen_liquidacion')->default(true);
            $table->boolean('preventa_nota_pedido')->default(true);
            $table->date('periodo_fecha_inicio')->nullable();
            $table->date('periodo_fecha_fin')->nullable();
            $table->decimal('comision_defecto', 5, 2)->nullable();
            $table->time('hora_cierre')->nullable();
            $table->boolean('comision_neto')->default(false);
            $table->date('preview_fecha_inicio')->nullable();
            $table->date('preview_fecha_fin')->nullable();
            $table->boolean('preview')->default(true);

            $table->string('imagen_login', 500)->nullable();
            $table->string('imagen_home', 500)->nullable();
            $table->string('imagen_reporte', 500)->nullable();
            $table->string('imagen_firma', 500)->nullable();

            $table->boolean('ventas_bloqueadas')->default(false);
            $table->boolean('compras_bloqueadas')->default(false);

            $table->timestamps();
        });

        DB::table('config_empresa')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('config_empresa');
    }
};
