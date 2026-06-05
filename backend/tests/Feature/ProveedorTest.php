<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Purchases\Models\Proveedor;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProveedorTest extends TestCase
{
    use RefreshDatabase;

    private string $compradorToken;
    private string $vendedorToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        DB::unprepared("
            INSERT INTO dim_documento_simbolo (codigo_sunat, nombre, simbolo) VALUES ('01', 'FACTURA', 'F') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_documento_tipo (codigo_sunat, nombre, simbolo_id, requiere_ruc) VALUES ('6', 'RUC', 1, true) ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_documento_tipo (codigo_sunat, nombre, simbolo_id, requiere_ruc) VALUES ('1', 'DNI', 2, false) ON CONFLICT (codigo_sunat) DO NOTHING;
        ");

        $comprador = User::factory()->create(['username' => 'comprador-p']);
        $comprador->assignRole('Comprador');
        $this->compradorToken = $comprador->createToken('test')->plainTextToken;

        $vendedor = User::factory()->create(['username' => 'vendedor-p']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;
    }

    private function asComprador(): static
    {
        return $this->withToken($this->compradorToken);
    }

    private function asVendedor(): static
    {
        return $this->withToken($this->vendedorToken);
    }

    public function test_list_proveedores(): void
    {
        Proveedor::create([
            'codigo' => 'PROV-A', 'tipo_documento_id' => 1,
            'numero_documento' => '20111111111',
            'nombre_completo' => 'Proveedor A', 'activo' => true,
        ]);
        $this->asComprador()
            ->getJson('/api/purchases/proveedores')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_create_proveedor(): void
    {
        $this->asComprador()
            ->postJson('/api/purchases/proveedores', [
                'tipo_documento_id' => 1,
                'numero_documento' => '20123456789',
                'nombre_completo' => 'Proveedor Nuevo',
            ])
            ->assertCreated()
            ->assertJsonPath('nombre_completo', 'Proveedor Nuevo');
    }

    public function test_create_proveedor_requires_nombre(): void
    {
        $this->asComprador()
            ->postJson('/api/purchases/proveedores', [
                'numero_documento' => '20123456789',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre_completo']);
    }

    public function test_update_proveedor(): void
    {
        $prov = Proveedor::create([
            'codigo' => 'PROV-U', 'tipo_documento_id' => 1,
            'numero_documento' => '20222222222',
            'nombre_completo' => 'Proveedor Original', 'activo' => true,
        ]);

        $this->asComprador()
            ->putJson("/api/purchases/proveedores/{$prov->id}", [
                'nombre_completo' => 'Proveedor Actualizado',
            ])
            ->assertOk()
            ->assertJsonPath('nombre_completo', 'Proveedor Actualizado');
    }

    public function test_delete_proveedor(): void
    {
        $prov = Proveedor::create([
            'codigo' => 'PROV-D', 'tipo_documento_id' => 1,
            'numero_documento' => '20333333333',
            'nombre_completo' => 'Proveedor Delete', 'activo' => true,
        ]);

        $this->asComprador()
            ->deleteJson("/api/purchases/proveedores/{$prov->id}")
            ->assertOk();

        $this->assertSoftDeleted('purchases_proveedores', ['id' => $prov->id]);
    }

    public function test_vendedor_no_puede_crear_proveedor(): void
    {
        $this->asVendedor()
            ->postJson('/api/purchases/proveedores', [
                'numero_documento' => '20111111111',
                'nombre_completo' => 'Vendedor Try',
            ])
            ->assertForbidden();
    }
}
