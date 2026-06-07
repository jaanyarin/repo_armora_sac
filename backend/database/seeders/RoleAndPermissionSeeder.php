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
            ['name' => 'ver-personal',     'descripcion' => 'Ver listado de personal',       'modulo' => 'Personal'],
            ['name' => 'crear-personal',   'descripcion' => 'Crear nuevos miembros de personal', 'modulo' => 'Personal'],
            ['name' => 'editar-personal',  'descripcion' => 'Editar personal existente',     'modulo' => 'Personal'],
            ['name' => 'eliminar-personal','descripcion' => 'Eliminar personal definitivamente (soft delete)', 'modulo' => 'Personal'],
            ['name' => 'generar-reportes-personal','descripcion' => 'Generar reportes PDF/listado de personal', 'modulo' => 'Personal'],
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
            ['name' => 'confirmar-ventas', 'descripcion' => 'Confirmar ventas (dispara descuento de stock)', 'modulo' => 'Sales'],
            ['name' => 'anular-ventas',    'descripcion' => 'Anular ventas',                  'modulo' => 'Sales'],
            ['name' => 'eliminar-ventas',  'descripcion' => 'Eliminar ventas definitivamente (físico)', 'modulo' => 'Sales'],
            ['name' => 'nota-credito',     'descripcion' => 'Emitir notas de crédito',        'modulo' => 'Sales'],
            ['name' => 'ver-stock',        'descripcion' => 'Consultar stock',                'modulo' => 'Inventory'],
            ['name' => 'ajustar-stock',    'descripcion' => 'Realizar ajustes de stock',      'modulo' => 'Inventory'],
            ['name' => 'kardex',           'descripcion' => 'Consultar kardex valorizado',    'modulo' => 'Inventory'],
            ['name' => 'ver-compras',      'descripcion' => 'Ver órdenes de compra',          'modulo' => 'Purchases'],
            ['name' => 'crear-compras',    'descripcion' => 'Crear órdenes de compra',        'modulo' => 'Purchases'],
            ['name' => 'editar-compras',   'descripcion' => 'Editar compras existentes',      'modulo' => 'Purchases'],
            ['name' => 'confirmar-compras','descripcion' => 'Confirmar compras (dispara aumento de stock)', 'modulo' => 'Purchases'],
            ['name' => 'anular-compras',   'descripcion' => 'Anular compras',                 'modulo' => 'Purchases'],
            ['name' => 'eliminar-compras', 'descripcion' => 'Eliminar compras definitivamente (físico)', 'modulo' => 'Purchases'],
            ['name' => 'ver-proveedores',  'descripcion' => 'Ver listado de proveedores',     'modulo' => 'Purchases'],
            ['name' => 'crear-proveedores','descripcion' => 'Crear nuevos proveedores',       'modulo' => 'Purchases'],
            ['name' => 'editar-proveedores','descripcion' => 'Editar proveedores existentes', 'modulo' => 'Purchases'],
            ['name' => 'eliminar-proveedores','descripcion' => 'Eliminar proveedores',         'modulo' => 'Purchases'],
            ['name' => 'aprobar-compras',  'descripcion' => 'Aprobar órdenes de compra',      'modulo' => 'Purchases'],
            ['name' => 'ver-finanzas',     'descripcion' => 'Ver módulo financiero',          'modulo' => 'Finance'],
            ['name' => 'enviar-sunat',     'descripcion' => 'Enviar comprobantes a SUNAT',    'modulo' => 'Finance'],
            ['name' => 'ver-rutas',        'descripcion' => 'Ver rutas de distribución',      'modulo' => 'Logistics'],
            ['name' => 'ver-dashboard',    'descripcion' => 'Acceder al dashboard principal', 'modulo' => 'Dashboard'],
            ['name' => 'ver-reportes',      'descripcion' => 'Generar reportes',               'modulo' => 'Dashboard'],
            ['name' => 'ver-configuracion', 'descripcion' => 'Ver configuración de empresa',  'modulo' => 'Company'],
            ['name' => 'configurar-empresa','descripcion' => 'Editar configuración de empresa','modulo' => 'Company'],
        ];

        foreach ($permissionDefs as $p) {
            $perm = Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                ['descripcion' => $p['descripcion'], 'modulo' => $p['modulo']],
            );
            $perm->descripcion = $p['descripcion'];
            $perm->modulo = $p['modulo'];
            $perm->save();
        }

        $roleDefs = [
            'Super-Admin'    => Permission::all()->pluck('name')->toArray(),
            'Admin'          => ['ver-usuarios','crear-usuarios','ver-personal','crear-personal','editar-personal','eliminar-personal','generar-reportes-personal','ver-clientes','crear-clientes','editar-clientes','eliminar-clientes','ver-productos','crear-productos','editar-productos','eliminar-productos','editar-precios','ver-ventas','crear-ventas','editar-ventas','confirmar-ventas','anular-ventas','eliminar-ventas','nota-credito','ver-stock','ajustar-stock','kardex','ver-compras','crear-compras','editar-compras','confirmar-compras','anular-compras','eliminar-compras','ver-proveedores','crear-proveedores','editar-proveedores','eliminar-proveedores','aprobar-compras','ver-finanzas','enviar-sunat','ver-rutas','ver-dashboard','ver-reportes','ver-configuracion','configurar-empresa'],
            'Gerente'        => ['ver-usuarios','ver-personal','generar-reportes-personal','ver-clientes','ver-productos','ver-ventas','confirmar-ventas','ver-stock','kardex','ver-compras','confirmar-compras','ver-proveedores','ver-finanzas','ver-dashboard','ver-reportes'],
            'Vendedor'       => ['ver-clientes','crear-clientes','ver-productos','ver-ventas','crear-ventas','editar-ventas','confirmar-ventas','nota-credito','ver-stock','ver-dashboard'],
            'Jefe-Almacen'   => ['ver-productos','ver-stock','ajustar-stock','kardex','ver-compras','confirmar-compras','ver-proveedores','ver-dashboard'],
            'Almacenero'     => ['ver-productos','ver-stock','ajustar-stock'],
            'Contador'       => ['ver-ventas','ver-compras','ver-finanzas','enviar-sunat','ver-proveedores','ver-dashboard','ver-reportes'],
            'Logistica'      => ['ver-rutas','ver-dashboard'],
            'Comprador'      => ['ver-productos','ver-compras','crear-compras','editar-compras','confirmar-compras','anular-compras','eliminar-compras','ver-proveedores','crear-proveedores','editar-proveedores','eliminar-proveedores','ver-dashboard'],
            'Portal-Cliente'    => ['ver-productos','ver-ventas'],
            'Portal-Proveedor'  => ['ver-compras'],
        ];

        foreach ($roleDefs as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
