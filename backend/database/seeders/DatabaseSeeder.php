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

        User::create([
            'codigo'          => 'ADMIN-001',
            'username'        => 'admin',
            'name'            => 'Admin',
            'nombre_completo' => 'Administrador del Sistema',
            'email'           => 'admin@armorasac.com',
            'dni'             => '12345678',
            'password'        => bcrypt('admin123'),
            'telefono'        => '999999999',
            'activo'          => true,
        ])->assignRole('Super-Admin');

        User::create([
            'codigo'          => 'VEND-001',
            'username'        => 'vendedor',
            'name'            => 'Vendedor',
            'nombre_completo' => 'Vendedor Demo',
            'email'           => 'vendedor@armorasac.com',
            'dni'             => '87654321',
            'password'        => bcrypt('vendedor123'),
            'activo'          => true,
        ])->assignRole('Vendedor');
    }
}
