<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Modules\Products\Models\Product;
use App\Modules\Sales\Models\Sale;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private string $vendedorToken;
    private string $adminToken;
    private string $logisticaToken;
    private int $clienteId;
    private int $productoId;
    private int $unidadMedidaId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedDimTables();

        $vendedor = User::factory()->create(['username' => 'vendedor']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;

        $admin = User::factory()->create(['username' => 'admin']);
        $admin->assignRole('Admin');
        $this->adminToken = $admin->createToken('test')->plainTextToken;

        $logistica = User::factory()->create(['username' => 'logistica']);
        $logistica->assignRole('Logistica');
        $this->logisticaToken = $logistica->createToken('test')->plainTextToken;

        $this->clienteId = Customer::create([
            'codigo' => 'CLI-001',
            'tipo_documento' => '1',
            'numero_documento' => '12345678',
            'nombre_completo' => 'Cliente Test',
            'activo' => true,
        ])->id;

        $this->productoId = Product::create([
            'codigo' => 'PROD-001',
            'nombre' => 'Producto Test',
            'unidad_medida_id' => $this->unidadMedidaId,
            'precio_venta' => 100,
            'stock_actual' => 50,
            'activo' => true,
        ])->id;
    }

    private function asVendedor(): static
    {
        $vendedor = User::where('username', 'vendedor')->first();
        return $this->actingAs($vendedor, 'sanctum');
    }

    private function asAdmin(): static
    {
        $admin = User::where('username', 'admin')->first();
        return $this->actingAs($admin, 'sanctum');
    }

    private function asLogistica(): static
    {
        $logistica = User::where('username', 'logistica')->first();
        return $this->actingAs($logistica, 'sanctum');
    }

    private function seedDimTables(): void
    {
        DB::unprepared("
            INSERT INTO dim_unidad_medida (codigo_sunat, nombre, simbolo) VALUES ('NIU', 'UNIDADES', 'UND') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_producto_clase (nombre) VALUES ('Producto Terminado') ON CONFLICT DO NOTHING;
            INSERT INTO dim_tipo_afeccion_igv (codigo_sunat, nombre, tributo_asociado) VALUES ('10', 'Gravado - Operación Onerosa', '1000') ON CONFLICT (codigo_sunat) DO NOTHING;
        ");
        $this->unidadMedidaId = DB::table('dim_unidad_medida')->where('codigo_sunat', 'NIU')->first()->id;
    }

    private function validSalePayload(): array
    {
        return [
            'cliente_id' => $this->clienteId,
            'fecha_emision' => now()->format('Y-m-d'),
            'estado' => 'borrador',
            'items' => [
                [
                    'producto_id' => $this->productoId,
                    'unidad_medida_id' => $this->unidadMedidaId,
                    'cantidad' => 2,
                    'precio_unitario' => 100,
                ],
            ],
        ];
    }

    public function test_list_sales(): void
    {
        $this->asVendedor()
            ->getJson('/api/sales')
            ->assertOk();
    }

    public function test_create_sale(): void
    {
        $response = $this->asVendedor()
            ->postJson('/api/sales', $this->validSalePayload());

        $response->assertCreated()
            ->assertJsonPath('cliente_id', $this->clienteId)
            ->assertJsonPath('estado', 'borrador');

        $this->assertDatabaseCount('sales_venta_items', 1);
    }

    public function test_create_sale_requires_cliente(): void
    {
        $payload = $this->validSalePayload();
        unset($payload['cliente_id']);

        $this->asVendedor()
            ->postJson('/api/sales', $payload)
            ->assertUnprocessable();
    }

    public function test_create_sale_requires_at_least_one_item(): void
    {
        $payload = $this->validSalePayload();
        $payload['items'] = [];

        $this->asVendedor()
            ->postJson('/api/sales', $payload)
            ->assertUnprocessable();
    }

    public function test_confirmar_sale_descuenta_stock(): void
    {
        $sale = $this->asVendedor()
            ->postJson('/api/sales', $this->validSalePayload())
            ->json();

        $stockInicial = Product::find($this->productoId)->stock_actual;

        $this->asVendedor()
            ->postJson("/api/sales/{$sale['id']}/confirmar")
            ->assertOk()
            ->assertJsonPath('estado', 'confirmada');

        $stockFinal = Product::find($this->productoId)->stock_actual;
        $this->assertEquals(48.0, (float) $stockFinal);
        $this->assertEquals(2.0, (float) ($stockInicial - $stockFinal));
    }

    public function test_anular_sale_revierte_stock(): void
    {
        $sale = $this->asVendedor()
            ->postJson('/api/sales', $this->validSalePayload())
            ->json();

        $this->asVendedor()
            ->postJson("/api/sales/{$sale['id']}/confirmar")
            ->assertOk();

        $response = $this->asAdmin()
            ->postJson("/api/sales/{$sale['id']}/anular");

        $response->assertOk()
            ->assertJsonPath('estado', 'anulada');

        $stockFinal = Product::find($this->productoId)->stock_actual;
        $this->assertEquals(50.0, (float) $stockFinal);
    }

    public function test_emitir_nota_credito(): void
    {
        $sale = $this->asVendedor()
            ->postJson('/api/sales', $this->validSalePayload())
            ->json();

        $this->asVendedor()
            ->postJson("/api/sales/{$sale['id']}/confirmar")
            ->assertOk();

        $this->asVendedor()
            ->postJson("/api/sales/{$sale['id']}/nota-credito", [
                'motivo' => 'Devolución de productos por defecto de fábrica',
            ])
            ->assertCreated();

        $this->assertDatabaseCount('sales_notas_credito', 1);
    }

    public function test_unauthorized_user_cannot_create_sale(): void
    {
        $this->asLogistica()
            ->postJson('/api/sales', $this->validSalePayload())
            ->assertForbidden();
    }
}
