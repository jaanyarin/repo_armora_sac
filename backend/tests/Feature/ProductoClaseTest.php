<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Products\Models\ProductoClase;
use App\Modules\Products\Models\ProductoSubclase;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoClaseTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $vendedorToken;
    private string $compradorToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create(['username' => 'admin-cl']);
        $admin->assignRole('Admin');
        $this->adminToken = $admin->createToken('test')->plainTextToken;

        $vendedor = User::factory()->create(['username' => 'vendedor-cl']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;

        $comprador = User::factory()->create(['username' => 'comprador-cl']);
        $comprador->assignRole('Comprador');
        $this->compradorToken = $comprador->createToken('test')->plainTextToken;
    }

    private function asAdmin(): static
    {
        return $this->withToken($this->adminToken);
    }

    private function asVendedor(): static
    {
        return $this->withToken($this->vendedorToken);
    }

    private function asComprador(): static
    {
        return $this->withToken($this->compradorToken);
    }

    public function test_list_clases_requires_auth(): void
    {
        $this->getJson('/api/products/clases')->assertUnauthorized();
    }

    public function test_list_clases_with_permission(): void
    {
        ProductoClase::factory()->count(3)->create();
        $this->asVendedor()
            ->getJson('/api/products/clases')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'codigo', 'nombre', 'slug', 'licor', 'orden', 'activo']], 'meta' => ['total']]);
    }

    public function test_list_clases_filter_by_search(): void
    {
        ProductoClase::factory()->create(['nombre' => 'PANASONIC UNIQUE TEST']);
        ProductoClase::factory()->create(['nombre' => 'BIMBO TEST']);
        $this->asVendedor()
            ->getJson('/api/products/clases?search=PANASONIC')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'PANASONIC UNIQUE TEST');
    }

    public function test_list_clases_filter_by_licor(): void
    {
        ProductoClase::factory()->create(['licor' => false]);
        ProductoClase::factory()->create(['licor' => false]);
        ProductoClase::factory()->create(['licor' => true]);
        $this->asVendedor()
            ->getJson('/api/products/clases?licor=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.licor', true);
    }

    public function test_show_clase(): void
    {
        $clase = ProductoClase::factory()->create();
        ProductoSubclase::factory()->count(2)->create(['clase_id' => $clase->id]);
        $this->asVendedor()
            ->getJson("/api/products/clases/{$clase->id}")
            ->assertOk()
            ->assertJsonPath('id', $clase->id)
            ->assertJsonPath('subclases_count', 2);
    }

    public function test_show_clase_404(): void
    {
        $this->asVendedor()
            ->getJson('/api/products/clases/01HZZZZZZZZZZZZZZZZZZZZZZZ')
            ->assertNotFound();
    }

    public function test_create_clase_requires_permission(): void
    {
        $this->asVendedor()
            ->postJson('/api/products/clases', ['nombre' => 'TEST'])
            ->assertForbidden();
    }

    public function test_create_clase_as_comprador(): void
    {
        $this->asComprador()
            ->postJson('/api/products/clases', [
                'nombre' => 'CLASE TEST COMPRADOR',
                'licor' => true,
                'descripcion' => 'Una clase de prueba',
            ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'CLASE TEST COMPRADOR')
            ->assertJsonPath('licor', true)
            ->assertJsonPath('activo', true);
    }

    public function test_create_clase_validates_nombre_required(): void
    {
        $this->asComprador()
            ->postJson('/api/products/clases', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_clase_validates_nombre_min(): void
    {
        $this->asComprador()
            ->postJson('/api/products/clases', ['nombre' => 'A'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_clase_unique_codigo(): void
    {
        ProductoClase::factory()->create(['codigo' => 'CLS-00001']);
        $this->asComprador()
            ->postJson('/api/products/clases', [
                'nombre' => 'OTRA CLASE',
                'codigo' => 'CLS-00001',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    public function test_update_clase(): void
    {
        $clase = ProductoClase::factory()->create();
        $this->asComprador()
            ->putJson("/api/products/clases/{$clase->id}", [
                'nombre' => 'NOMBRE ACTUALIZADO',
                'licor' => true,
            ])
            ->assertOk()
            ->assertJsonPath('nombre', 'NOMBRE ACTUALIZADO')
            ->assertJsonPath('licor', true);
    }

    public function test_update_clase_requires_permission(): void
    {
        $clase = ProductoClase::factory()->create();
        $this->asVendedor()
            ->putJson("/api/products/clases/{$clase->id}", ['nombre' => 'NO PUEDO'])
            ->assertForbidden();
    }

    public function test_delete_clase_cascades_to_subclases(): void
    {
        $clase = ProductoClase::factory()->create();
        ProductoSubclase::factory()->count(3)->create(['clase_id' => $clase->id]);

        $this->asComprador()
            ->deleteJson("/api/products/clases/{$clase->id}")
            ->assertOk()
            ->assertJsonPath('subclases_eliminadas', 3);

        $this->assertSoftDeleted('products_clases', ['id' => $clase->id]);
        $this->assertEquals(0, ProductoSubclase::where('clase_id', $clase->id)->count());
    }

    public function test_delete_clase_requires_permission(): void
    {
        $clase = ProductoClase::factory()->create();
        $this->asVendedor()
            ->deleteJson("/api/products/clases/{$clase->id}")
            ->assertForbidden();
    }

    public function test_reorder_clases(): void
    {
        $a = ProductoClase::factory()->create(['orden' => 1]);
        $b = ProductoClase::factory()->create(['orden' => 2]);
        $c = ProductoClase::factory()->create(['orden' => 3]);

        $this->asComprador()
            ->postJson('/api/products/clases/reorder', [
                'ids' => [$c->id, $a->id, $b->id],
            ])
            ->assertOk();

        $this->assertEquals(1, ProductoClase::find($c->id)->orden);
        $this->assertEquals(2, ProductoClase::find($a->id)->orden);
        $this->assertEquals(3, ProductoClase::find($b->id)->orden);
    }

    public function test_reorder_requires_permission(): void
    {
        $a = ProductoClase::factory()->create();
        $this->asVendedor()
            ->postJson('/api/products/clases/reorder', ['ids' => [$a->id]])
            ->assertForbidden();
    }

    public function test_reorder_validates_ids(): void
    {
        $this->asComprador()
            ->postJson('/api/products/clases/reorder', ['ids' => ['invalid-id']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids.0']);
    }
}
