<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_documento_identidad', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 4)->unique();
            $table->string('nombre', 50);
            $table->string('longitud', 20)->nullable();
            $table->string('regex', 100)->nullable();
            $table->string('pais_codigo', 4)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        DB::table('dim_documento_identidad')->insert([
            ['codigo' => 'DNI',  'nombre' => 'Documento Nacional de Identidad', 'longitud' => '8',   'regex' => '/^\d{8}$/',         'pais_codigo' => 'PE', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'CE',   'nombre' => 'Carné de Extranjería',             'longitud' => '12',  'regex' => '/^[A-Z0-9]{1,12}$/', 'pais_codigo' => null, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'PAS',  'nombre' => 'Pasaporte',                        'longitud' => '20',  'regex' => '/^[A-Z0-9]{1,20}$/', 'pais_codigo' => null, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'RUC',  'nombre' => 'Registro Único de Contribuyentes', 'longitud' => '11',  'regex' => '/^\d{11}$/',        'pais_codigo' => 'PE', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_documento_identidad');
    }
};
