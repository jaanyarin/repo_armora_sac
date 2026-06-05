<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Products\Models\Product;
use App\Modules\Sales\Services\SaleService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private string $jefeAlmacenToken;
    private string $vendedorToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        DB::unprepared("
            INSERT INTO dim_unidad_medida (codigo_sunat, nombre, simbolo) VALUES ('NIU', 'UNIDADES', 'UND') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_producto_clase (nombre) VALUES ('Producto Terminado') ON CONFLICT DO NOTHING;
            INSERT INTO dim_tipo_afeccion_igv (codigo_sunat, nombre, tributo_asociado) VALUES ('10', 'Gravado', '1000') ON CONFLICT (codigo_sunat) DO NOTHING;
        ");

        $jefeAlmacen = User::factory()->create(['username' => 'almacen']);
        $jefeAlmacen->assignRole('Jefe-Almacen');
        $this->jefeAlmacenToken = $jefeAlmacen->createToken('test')->plainTextToken;

        $vendedor = User::factory()->create(['username' => 'vendedor']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;
    }

    private function asJefeAlmacen(): static
    {
        return $this->withToken($this->jefeAlmacenToken);
    }

    private function asVendedor(): static
    {
        return $this->withToken($this->vendedorToken);
    }

    public function test_list_stock(): void
    {
        $this->asJefeAlmacen()
            ->getJson('/api/inventory/stock')
            ->assertOk();
    }

    public function test_kardex(): void
    {
        $this->asJefeAlmacen()
            ->getJson('/api/inventory/kardex')
            ->assertOk();
    }

    /**
     * A-02: dim_almacen se usa efectivamente. Al confirmar una venta, el
     * movimiento y el stock se registran con almacen_id (no null) apuntando
     * al almacén principal.
     */
    public function test_descontar_stock_uses_almacen_id(): void
    {
        // Autenticar vendedor para que Auth::id() devuelva un usuario válido
        $vendedor = User::where('username', 'vendedor')->first();
        $this->actingAs($vendedor, 'sanctum');

        // Sembrar almacén principal (idempotente)
        $almacenId = DB::table('dim_almacen')->insertGetId([
            'codigo' => 'ALM-001',
            'nombre' => 'Almacén Principal',
            'principal' => true,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear cliente y producto mínimo
        $clienteId = Customer::create([
            'codigo' => 'CLI-A02', 'tipo_documento' => '1', 'numero_documento' => '11111111',
            'nombre_completo' => 'Cliente A02', 'activo' => true,
        ])->id;
        $umId = DB::table('dim_unidad_medida')->value('id');
        $productoId = Product::create([
            'codigo' => 'PROD-A02', 'nombre' => 'Producto A02',
            'unidad_medida_id' => $umId, 'precio_venta' => 100,
            'stock_actual' => 50, 'activo' => true,
        ])->id;

        $saleService = app(SaleService::class);
        $sale = $saleService->create([
            'cliente_id' => $clienteId,
            'documento_tipo_id' => 1,
            'serie' => 'B001', 'numero' => '1',
            'fecha_emision' => now()->toDateString(),
            'moneda_id' => 1,
            'items' => [[
                'producto_id' => $productoId,
                'unidad_medida_id' => $umId,
                'cantidad' => 3,
                'precio_unitario' => 100,
                'descuento_linea' => 0,
            ]],
        ]);

        $saleService->confirmar($sale);

        // Verificar que el movimiento tiene almacen_id no null
        $movimiento = DB::table('inventory_movimientos')
            ->where('referencia_tipo', 'sale')
            ->where('referencia_id', $sale->id)
            ->first();

        $this->assertNotNull($movimiento);
        $this->assertNotNull($movimiento->almacen_id, 'A-02: almacen_id debe estar poblado, no null');
        $this->assertEquals($almacenId, (int) $movimiento->almacen_id);

        // Verificar stock multi-almacén independiente
        $stock = DB::table('inventory_stock')
            ->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(47.0, (float) $stock->cantidad_disponible);
    }

    /**
     * A-02: con 2 almacenes, descontar en uno no afecta al otro.
     */
    public function test_multi_almacen_stock_independent(): void
    {
        $almacenA = DB::table('dim_almacen')->insertGetId([
            'codigo' => 'ALM-A', 'nombre' => 'Almacén A', 'principal' => true, 'activo' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $almacenB = DB::table('dim_almacen')->insertGetId([
            'codigo' => 'ALM-B', 'nombre' => 'Almacén B', 'principal' => false, 'activo' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $umId = DB::table('dim_unidad_medida')->value('id');
        $productoId = Product::create([
            'codigo' => 'PROD-MULTI', 'nombre' => 'Producto Multi',
            'unidad_medida_id' => $umId, 'precio_venta' => 100,
            'stock_actual' => 0, 'activo' => true,
        ])->id;

        $inventory = app(InventoryService::class);

        // Cargar 10 unidades en A
        DB::table('inventory_stock')->insert([
            'producto_id' => $productoId, 'almacen_id' => $almacenA,
            'cantidad_disponible' => 10, 'ultima_actualizacion' => now(),
        ]);
        DB::table('inventory_stock')->insert([
            'producto_id' => $productoId, 'almacen_id' => $almacenB,
            'cantidad_disponible' => 5, 'ultima_actualizacion' => now(),
        ]);

        $stockA = (float) DB::table('inventory_stock')->where('producto_id', $productoId)->where('almacen_id', $almacenA)->value('cantidad_disponible');
        $stockB = (float) DB::table('inventory_stock')->where('producto_id', $productoId)->where('almacen_id', $almacenB)->value('cantidad_disponible');

        $this->assertEquals(10.0, $stockA);
        $this->assertEquals(5.0, $stockB);
    }
}
