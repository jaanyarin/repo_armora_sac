<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Purchases\Models\Compra;
use App\Modules\Purchases\Models\CompraItem;
use App\Modules\Purchases\Models\Proveedor;
use App\Modules\Customers\Models\Customer;
use App\Modules\Products\Models\Product;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    private int $compradorId;
    private int $vendedorId;
    private int $adminId;
    private string $proveedorId;
    private int $productoId;
    private int $unidadMedidaId;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RoleAndPermissionSeeder::class);
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seedDimTables();

        $comprador = User::factory()->create(['username' => 'comprador']);
        $comprador->assignRole('Comprador');
        $this->compradorId = $comprador->id;

        $admin = User::factory()->create(['username' => 'admin-c']);
        $admin->assignRole('Admin');
        $this->adminId = $admin->id;

        $vendedor = User::factory()->create(['username' => 'vendedor-c']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorId = $vendedor->id;

        $this->unidadMedidaId = DB::table('dim_unidad_medida')->value('id');
        $this->productoId = Product::create([
            'codigo' => 'PROD-C', 'nombre' => 'Producto C',
            'unidad_medida_id' => $this->unidadMedidaId, 'precio_venta' => 50,
            'stock_actual' => 10, 'activo' => true,
        ])->id;
        $this->proveedorId = Proveedor::create([
            'codigo' => 'PROV-1', 'tipo_documento_id' => 6,
            'numero_documento' => '20123456789',
            'nombre_completo' => 'Proveedor Uno', 'activo' => true,
        ])->id;
    }

    private function seedDimTables(): void
    {
        DB::unprepared("
            INSERT INTO dim_unidad_medida (codigo_sunat, nombre, simbolo) VALUES ('NIU', 'UNIDADES', 'UND') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_producto_clase (nombre) VALUES ('Producto Terminado') ON CONFLICT DO NOTHING;
            INSERT INTO dim_tipo_afeccion_igv (codigo_sunat, nombre, tributo_asociado) VALUES ('10', 'Gravado', '1000') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_moneda (codigo, nombre, simbolo) VALUES ('PEN', 'Soles', 'S/') ON CONFLICT (codigo) DO NOTHING;
            INSERT INTO dim_documento_simbolo (codigo_sunat, nombre, simbolo) VALUES ('01', 'FACTURA', 'F') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_documento_tipo (codigo_sunat, nombre, simbolo_id, requiere_ruc) VALUES ('01', 'FACTURA', 1, true) ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_documento_tipo (codigo_sunat, nombre, simbolo_id, requiere_ruc) VALUES ('6', 'RUC', 1, true) ON CONFLICT (codigo_sunat) DO NOTHING;
        ");
    }

    private function asComprador(): static
    {
        return $this->actingAs(User::find($this->compradorId), 'sanctum');
    }

    private function asVendedor(): static
    {
        return $this->actingAs(User::find($this->vendedorId), 'sanctum');
    }

    private function asAdmin(): static
    {
        return $this->actingAs(User::find($this->adminId), 'sanctum');
    }

    private function validPayload(): array
    {
        return [
            'proveedor_id' => $this->proveedorId,
            'documento_tipo_id' => 1,
            'serie' => 'F001',
            'numero' => '1',
            'fecha_emision' => now()->toDateString(),
            'moneda_id' => 1,
            'items' => [[
                'producto_id' => $this->productoId,
                'unidad_medida_id' => $this->unidadMedidaId,
                'cantidad' => 5,
                'precio_unitario' => 50,
                'descuento_linea' => 0,
            ]],
        ];
    }

    public function test_list_compras(): void
    {
        $this->asComprador()->postJson('/api/purchases/compras', $this->validPayload())->assertCreated();
        $this->asComprador()->getJson('/api/purchases/compras')->assertOk()->assertJsonStructure(['data', 'meta']);
    }

    public function test_create_compra_with_borrador(): void
    {
        $response = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('estado', 'borrador');

        $data = $response->json();
        $this->assertEquals(211.86, $data['subtotal']); // 5 * 50 / 1.18 = 211.86
        $this->assertEquals(38.14, $data['igv']);
        $this->assertEquals(250.0, $data['total']);
    }

    public function test_create_compra_requires_proveedor(): void
    {
        $payload = $this->validPayload();
        unset($payload['proveedor_id']);
        $this->asComprador()
            ->postJson('/api/purchases/compras', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['proveedor_id']);
    }

    public function test_create_compra_requires_at_least_one_item(): void
    {
        $payload = $this->validPayload();
        $payload['items'] = [];
        $this->asComprador()
            ->postJson('/api/purchases/compras', $payload)
            ->assertUnprocessable();
    }

    public function test_confirmar_compra_aumenta_stock(): void
    {
        $compra = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->json();

        $stockInicial = (float) Product::find($this->productoId)->stock_actual;

        $this->asComprador()
            ->postJson("/api/purchases/compras/{$compra['id']}/confirmar")
            ->assertOk()
            ->assertJsonPath('estado', 'confirmada');

        $stockFinal = (float) Product::find($this->productoId)->stock_actual;
        $this->assertEquals(5.0, $stockFinal - $stockInicial, 'Stock debe aumentar en 5');
    }

    public function test_anular_compra_resets_saldo(): void
    {
        $compra = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->json();

        $response = $this->asComprador()
            ->postJson("/api/purchases/compras/{$compra['id']}/anular")
            ->assertOk()
            ->assertJsonPath('estado', 'anulada');
        $this->assertEquals(0, (float) $response->json('saldo_pendiente'));
    }

    public function test_compra_with_soft_deleted_proveedor_is_rejected(): void
    {
        $softDeleted = Proveedor::create([
            'codigo' => 'PROV-SOFT', 'tipo_documento_id' => 6,
            'numero_documento' => '20999999999',
            'nombre_completo' => 'Proveedor Soft', 'activo' => true,
        ]);
        $softDeleted->delete();

        $payload = $this->validPayload();
        $payload['proveedor_id'] = $softDeleted->id;
        $this->asComprador()
            ->postJson('/api/purchases/compras', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['proveedor_id']);
    }

    public function test_vendedor_cannot_confirmar_compra(): void
    {
        $compra = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->json();

        $this->asVendedor()
            ->postJson("/api/purchases/compras/{$compra['id']}/confirmar")
            ->assertForbidden();
    }

    public function test_vendedor_cannot_delete_compra(): void
    {
        $compra = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->json();

        $this->asVendedor()
            ->deleteJson("/api/purchases/compras/{$compra['id']}")
            ->assertForbidden();
    }

    public function test_compra_borrador_puede_ser_editada(): void
    {
        $compra = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->json();

        $payload = $this->validPayload();
        $payload['observaciones'] = 'Editada';
        $this->asComprador()
            ->putJson("/api/purchases/compras/{$compra['id']}", $payload)
            ->assertOk()
            ->assertJsonPath('observaciones', 'Editada');
    }

    public function test_compra_confirmada_no_puede_ser_editada(): void
    {
        $compra = $this->asComprador()
            ->postJson('/api/purchases/compras', $this->validPayload())
            ->json();

        $this->asComprador()
            ->postJson("/api/purchases/compras/{$compra['id']}/confirmar")
            ->assertOk();

        $payload = $this->validPayload();
        $payload['observaciones'] = 'Intento post-confirmar';
        $this->asComprador()
            ->putJson("/api/purchases/compras/{$compra['id']}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['estado']);
    }

    public function test_igv_calculation_uses_sales_formula(): void
    {
        // Mismo cálculo que el test IGV de SalesTest:
        // cantidad=10, precio=100 → subtotal = 1000/1.18 = 847.46
        $payload = $this->validPayload();
        $payload['items'][0]['cantidad'] = 10;
        $payload['items'][0]['precio_unitario'] = 100;

        $response = $this->asComprador()
            ->postJson('/api/purchases/compras', $payload)
            ->assertCreated()
            ->json();

        $this->assertEquals(847.46, $response['subtotal']);
        $this->assertEquals(152.54, $response['igv']);
        $this->assertEquals(1000.0, $response['total']);
    }

    public function test_paginate_uses_index_and_search(): void
    {
        // Crear 3 compras
        for ($i = 0; $i < 3; $i++) {
            $payload = $this->validPayload();
            $payload['numero'] = (string) ($i + 1);
            $this->asComprador()->postJson('/api/purchases/compras', $payload)->assertCreated();
        }
        $response = $this->asComprador()
            ->getJson('/api/purchases/compras?per_page=2')
            ->assertOk();
        $this->assertCount(2, $response->json('data'));
    }
}
