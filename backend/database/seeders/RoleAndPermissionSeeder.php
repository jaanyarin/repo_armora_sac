<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionDefs = [
            ['name' => 'ver-usuarios',     'descripcion' => 'Ver listado de usuarios',       'modulo' => 'Auth'],
            ['name' => 'crear-usuarios',   'descripcion' => 'Crear nuevos usuarios',         'modulo' => 'Auth'],
            ['name' => 'asignar-roles',    'descripcion' => 'Asignar roles a usuarios',      'modulo' => 'Auth'],
            ['name' => 'ver-clientes',     'descripcion' => 'Ver listado de clientes',       'modulo' => 'Customers'],
            ['name' => 'crear-clientes',   'descripcion' => 'Crear nuevos clientes',         'modulo' => 'Customers'],
            ['name' => 'editar-clientes',     'descripcion' => 'Editar clientes existentes',         'modulo' => 'Customers'],
            ['name' => 'eliminar-clientes',  'descripcion' => 'Eliminar clientes',                 'modulo' => 'Customers'],
            ['name' => 'ver-productos',    'descripcion' => 'Ver catálogo de productos',     'modulo' => 'Products'],
            ['name' => 'crear-productos',  'descripcion' => 'Crear productos',                'modulo' => 'Products'],
            ['name' => 'editar-productos', 'descripcion' => 'Editar productos existentes',    'modulo' => 'Products'],
            ['name' => 'eliminar-productos','descripcion' => 'Eliminar productos',             'modulo' => 'Products'],
            ['name' => 'editar-precios',   'descripcion' => 'Modificar precios',              'modulo' => 'Products'],
            ['name' => 'ver-ventas',       'descripcion' => 'Ver ventas realizadas',          'modulo' => 'Sales'],
            ['name' => 'crear-ventas',     'descripcion' => 'Crear nuevas ventas',            'modulo' => 'Sales'],
            ['name' => 'editar-ventas',    'descripcion' => 'Editar ventas existentes',       'modulo' => 'Sales'],
            ['name' => 'anular-ventas',    'descripcion' => 'Anular ventas',                  'modulo' => 'Sales'],
            ['name' => 'nota-credito',     'descripcion' => 'Emitir notas de crédito',        'modulo' => 'Sales'],
            ['name' => 'ver-stock',        'descripcion' => 'Consultar stock',                'modulo' => 'Inventory'],
            ['name' => 'ajustar-stock',    'descripcion' => 'Realizar ajustes de stock',      'modulo' => 'Inventory'],
            ['name' => 'kardex',           'descripcion' => 'Consultar kardex valorizado',    'modulo' => 'Inventory'],
            ['name' => 'ver-compras',      'descripcion' => 'Ver órdenes de compra',          'modulo' => 'Purchases'],
            ['name' => 'crear-compras',    'descripcion' => 'Crear órdenes de compra',        'modulo' => 'Purchases'],
            ['name' => 'aprobar-compras',  'descripcion' => 'Aprobar órdenes de compra',      'modulo' => 'Purchases'],
            ['name' => 'ver-finanzas',     'descripcion' => 'Ver módulo financiero',          'modulo' => 'Finance'],
            ['name' => 'enviar-sunat',     'descripcion' => 'Enviar comprobantes a SUNAT',    'modulo' => 'Finance'],
            ['name' => 'ver-rutas',        'descripcion' => 'Ver rutas de distribución',      'modulo' => 'Logistics'],
            ['name' => 'ver-dashboard',    'descripcion' => 'Acceder al dashboard principal', 'modulo' => 'Dashboard'],
            ['name' => 'ver-reportes',     'descripcion' => 'Generar reportes',               'modulo' => 'Dashboard'],
        ];

        foreach ($permissionDefs as $p) {
            $perm = Permission::create(['name' => $p['name'], 'guard_name' => 'web']);
            $perm->descripcion = $p['descripcion'];
            $perm->modulo = $p['modulo'];
            $perm->save();
        }

        $roleDefs = [
            'Super-Admin'    => Permission::all()->pluck('name')->toArray(),
            'Admin'          => ['ver-usuarios','crear-usuarios','ver-clientes','crear-clientes','editar-clientes','eliminar-clientes','ver-productos','crear-productos','editar-productos','eliminar-productos','editar-precios','ver-ventas','crear-ventas','anular-ventas','nota-credito','ver-stock','ajustar-stock','kardex','ver-compras','crear-compras','aprobar-compras','ver-finanzas','enviar-sunat','ver-rutas','ver-dashboard','ver-reportes'],
            'Gerente'        => ['ver-usuarios','ver-clientes','ver-productos','ver-ventas','ver-stock','kardex','ver-compras','ver-finanzas','ver-dashboard','ver-reportes'],
            'Vendedor'       => ['ver-clientes','crear-clientes','ver-productos','ver-ventas','crear-ventas','editar-ventas','nota-credito','ver-stock','ver-dashboard'],
            'Jefe-Almacen'   => ['ver-productos','ver-stock','ajustar-stock','kardex','ver-dashboard'],
            'Almacenero'     => ['ver-productos','ver-stock','ajustar-stock'],
            'Contador'       => ['ver-ventas','ver-compras','ver-finanzas','enviar-sunat','ver-dashboard','ver-reportes'],
            'Logistica'      => ['ver-rutas','ver-dashboard'],
            'Comprador'      => ['ver-productos','ver-compras','crear-compras','ver-dashboard'],
            'Portal-Cliente'    => ['ver-productos','ver-ventas'],
            'Portal-Proveedor'  => ['ver-compras'],
        ];

        foreach ($roleDefs as $roleName => $permissions) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($permissions);
        }
    }
}
