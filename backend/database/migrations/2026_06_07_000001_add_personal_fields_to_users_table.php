<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellido_paterno', 100)->nullable()->after('nombre_completo');
            $table->string('apellido_materno', 100)->nullable()->after('apellido_paterno');
            $table->string('nombres', 150)->nullable()->after('apellido_materno');

            $table->unsignedBigInteger('documento_tipo_id')->nullable()->after('ruc');
            $table->string('numero_documento', 20)->nullable()->after('documento_tipo_id');

            $table->unsignedBigInteger('sexo_id')->nullable()->after('numero_documento');
            $table->unsignedBigInteger('estado_civil_id')->nullable()->after('sexo_id');
            $table->date('fecha_nacimiento')->nullable()->after('estado_civil_id');

            $table->unsignedBigInteger('pais_id')->nullable()->after('fecha_nacimiento');
            $table->string('telefono_fijo', 20)->nullable()->after('telefono');
            $table->string('telefono_celular', 20)->nullable()->after('telefono_fijo');

            $table->unsignedBigInteger('departamento_id')->nullable()->after('telefono_celular');
            $table->unsignedBigInteger('provincia_id')->nullable()->after('departamento_id');
            $table->unsignedBigInteger('ubigeo_id')->nullable()->after('provincia_id');
            $table->text('direccion')->nullable()->after('ubigeo_id');
            $table->text('referencia')->nullable()->after('direccion');

            $table->string('foto_path')->nullable()->after('referencia');

            $table->timestamp('password_changed_at')->nullable()->after('ultimo_acceso');
            $table->string('last_login_ip', 45)->nullable()->after('password_changed_at');

            $table->softDeletes();

            $table->foreign('documento_tipo_id')->references('id')->on('dim_documento_tipo')->nullOnDelete();
            $table->foreign('sexo_id')->references('id')->on('dim_sexo')->nullOnDelete();
            $table->foreign('estado_civil_id')->references('id')->on('dim_estado_civil')->nullOnDelete();
            $table->foreign('pais_id')->references('id')->on('dim_pais')->nullOnDelete();
            $table->foreign('departamento_id')->references('id')->on('dim_departamento')->nullOnDelete();
            $table->foreign('provincia_id')->references('id')->on('dim_provincia')->nullOnDelete();
            $table->foreign('ubigeo_id')->references('id')->on('dim_ubigeo')->nullOnDelete();

            $table->index('numero_documento');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['documento_tipo_id']);
            $table->dropForeign(['sexo_id']);
            $table->dropForeign(['estado_civil_id']);
            $table->dropForeign(['pais_id']);
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['provincia_id']);
            $table->dropForeign(['ubigeo_id']);

            $table->dropIndex(['numero_documento']);
            $table->dropIndex(['deleted_at']);

            $table->dropColumn([
                'apellido_paterno',
                'apellido_materno',
                'nombres',
                'documento_tipo_id',
                'numero_documento',
                'sexo_id',
                'estado_civil_id',
                'fecha_nacimiento',
                'pais_id',
                'telefono_fijo',
                'telefono_celular',
                'departamento_id',
                'provincia_id',
                'ubigeo_id',
                'direccion',
                'referencia',
                'foto_path',
                'password_changed_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
