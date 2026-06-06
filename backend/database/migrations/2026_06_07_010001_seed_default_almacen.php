<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('dim_almacen')->insertOrIgnore([
            [
                'codigo' => 'ALM-001',
                'nombre' => 'Almacén Principal',
                'direccion' => 'Av. Principal 123, Lima',
                'principal' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('dim_almacen')->where('codigo', 'ALM-001')->delete();
    }
};
