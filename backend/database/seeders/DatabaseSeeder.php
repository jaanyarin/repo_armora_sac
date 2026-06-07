<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        User::updateOrCreate([
            'username' => 'admin',
        ], [
            'codigo'          => 'ADMIN-001',
            'name'            => 'Admin',
            'nombre_completo' => 'Administrador del Sistema',
            'email'           => 'admin@armorasac.com',
            'documento_identidad_id' => 1,
            'numero_documento' => '12345678',
            'password'        => bcrypt('admin123'),
            'telefono'        => '999999999',
            'activo'          => true,
        ])->assignRole('Super-Admin');

        User::updateOrCreate([
            'username' => 'vendedor',
        ], [
            'codigo'          => 'VEND-001',
            'name'            => 'Vendedor',
            'nombre_completo' => 'Vendedor Demo',
            'email'           => 'vendedor@armorasac.com',
            'documento_identidad_id' => 1,
            'numero_documento' => '87654321',
            'password'        => bcrypt('vendedor123'),
            'activo'          => true,
        ])->assignRole('Vendedor');
    }
}
