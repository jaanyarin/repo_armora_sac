<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Products\Models\ProductoClase;
use App\Modules\Products\Models\ProductoSubclase;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoSubclaseTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $vendedorToken;
    private string $compradorToken;
    private string $contadorToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create(['username' => 'admin-scl']);
        $admin->assignRole('Admin');
        $this->adminToken = $admin->createToken('test')->plainTextToken;

        $vendedor = User::factory()->create(['username' => 'vendedor-scl']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;

        $comprador = User::factory()->create(['username' => 'comprador-scl']);
        $comprador->assignRole('Comprador');
        $this->compradorToken = $comprador->createToken('test')->plainTextToken;

        $contador = User::factory()->create(['username' => 'contador-scl']);
        $contador->assignRole('Contador');
        $this->contadorToken = $contador->createToken('test')->plainTextToken;
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

    private function asContador(): static
    {
        return $this->withToken($this->contadorToken);
    }

    public function test_list_subclases_requires_auth(): void
    {
        $this->getJson('/api/products/subclases')->assertUnauthorized();
    }

    public function test_list_subclases_with_permission(): void
    {
        $clase = ProductoClase::factory()->create();
        ProductoSubclase::factory()->count(2)->create(['clase_id' => $clase->id]);
        $this->asVendedor()
            ->getJson('/api/products/subclases')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'clase_id', 'codigo', 'nombre', 'orden', 'activo']], 'meta' => ['total']]);
    }

    public function test_list_subclases_filter_by_clase_id(): void
    {
        $clase1 = ProductoClase::factory()->create();
        $clase2 = ProductoClase::factory()->create();
        ProductoSubclase::factory()->count(2)->create(['clase_id' => $clase1->id]);
        ProductoSubclase::factory()->count(3)->create(['clase_id' => $clase2->id]);

        $this->asVendedor()
            ->getJson("/api/products/subclases?clase_id={$clase1->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_subclases_filter_by_search(): void
    {
        $clase = ProductoClase::factory()->create();
        ProductoSubclase::factory()->create(['clase_id' => $clase->id, 'nombre' => 'BATERIAS PANASONIC TEST']);
        ProductoSubclase::factory()->create(['clase_id' => $clase->id, 'nombre' => 'PILAS ALCALINAS TEST']);

        $this->asVendedor()
            ->getJson('/api/products/subclases?search=BATERIAS')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'BATERIAS PANASONIC TEST');
    }

    public function test_show_subclase(): void
    {
        $clase = ProductoClase::factory()->create();
        $subclase = ProductoSubclase::factory()->create(['clase_id' => $clase->id]);
        $this->asVendedor()
            ->getJson("/api/products/subclases/{$subclase->id}")
            ->assertOk()
            ->assertJsonPath('id', $subclase->id)
            ->assertJsonPath('clase.id', $clase->id);
    }

    public function test_show_subclase_404(): void
    {
        $this->asVendedor()
            ->getJson('/api/products/subclases/01HZZZZZZZZZZZZZZZZZZZZZZZ')
            ->assertNotFound();
    }

    public function test_create_subclase_requires_permission(): void
    {
        $clase = ProductoClase::factory()->create();
        $this->asVendedor()
            ->postJson('/api/products/subclases', [
                'clase_id' => $clase->id,
                'nombre' => 'TEST',
            ])
            ->assertForbidden();
    }

    public function test_create_subclase_as_comprador(): void
    {
        $clase = ProductoClase::factory()->create();
        $this->asComprador()
            ->postJson('/api/products/subclases', [
                'clase_id' => $clase->id,
                'nombre' => 'SUBCLASE TEST COMPRADOR',
                'descripcion' => 'Una subclase de prueba',
            ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'SUBCLASE TEST COMPRADOR')
            ->assertJsonPath('clase_id', $clase->id);
    }

    public function test_create_subclase_validates_clase_required(): void
    {
        $this->asComprador()
            ->postJson('/api/products/subclases', ['nombre' => 'TEST'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['clase_id']);
    }

    public function test_create_subclase_validates_nombre_required(): void
    {
        $clase = ProductoClase::factory()->create();
        $this->asComprador()
            ->postJson('/api/products/subclases', ['clase_id' => $clase->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_subclase_validates_clase_exists(): void
    {
        $this->asComprador()
            ->postJson('/api/products/subclases', [
                'clase_id' => '01HZZZZZZZZZZZZZZZZZZZZZZZ',
                'nombre' => 'TEST',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['clase_id']);
    }

    public function test_create_subclase_unique_per_clase(): void
    {
        $clase = ProductoClase::factory()->create();
        ProductoSubclase::factory()->create(['clase_id' => $clase->id, 'nombre' => 'BATERIAS TEST']);
        $this->asComprador()
            ->postJson('/api/products/subclases', [
                'clase_id' => $clase->id,
                'nombre' => 'BATERIAS TEST',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_subclase_same_name_different_clase_allowed(): void
    {
        $clase1 = ProductoClase::factory()->create();
        $clase2 = ProductoClase::factory()->create();
        ProductoSubclase::factory()->create(['clase_id' => $clase1->id, 'nombre' => 'BATERIAS COMPARTIDAS']);
        $this->asComprador()
            ->postJson('/api/products/subclases', [
                'clase_id' => $clase2->id,
                'nombre' => 'BATERIAS COMPARTIDAS',
            ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'BATERIAS COMPARTIDAS');
    }

    public function test_update_subclase(): void
    {
        $clase = ProductoClase::factory()->create();
        $subclase = ProductoSubclase::factory()->create(['clase_id' => $clase->id]);
        $this->asComprador()
            ->putJson("/api/products/subclases/{$subclase->id}", [
                'clase_id' => $clase->id,
                'nombre' => 'NOMBRE ACTUALIZADO',
            ])
            ->assertOk()
            ->assertJsonPath('nombre', 'NOMBRE ACTUALIZADO');
    }

    public function test_update_subclase_requires_permission(): void
    {
        $clase = ProductoClase::factory()->create();
        $subclase = ProductoSubclase::factory()->create(['clase_id' => $clase->id]);
        $this->asVendedor()
            ->putJson("/api/products/subclases/{$subclase->id}", [
                'clase_id' => $clase->id,
                'nombre' => 'NO PUEDO',
            ])
            ->assertForbidden();
    }

    public function test_delete_subclase_soft_delete(): void
    {
        $subclase = ProductoSubclase::factory()->create();
        $this->asComprador()
            ->deleteJson("/api/products/subclases/{$subclase->id}")
            ->assertOk();
        $this->assertSoftDeleted('products_subclases', ['id' => $subclase->id]);
    }

    public function test_delete_subclase_requires_permission(): void
    {
        $subclase = ProductoSubclase::factory()->create();
        $this->asVendedor()
            ->deleteJson("/api/products/subclases/{$subclase->id}")
            ->assertForbidden();
    }

    public function test_reorder_subclases(): void
    {
        $clase = ProductoClase::factory()->create();
        $a = ProductoSubclase::factory()->create(['clase_id' => $clase->id, 'orden' => 1]);
        $b = ProductoSubclase::factory()->create(['clase_id' => $clase->id, 'orden' => 2]);
        $c = ProductoSubclase::factory()->create(['clase_id' => $clase->id, 'orden' => 3]);

        $this->asComprador()
            ->postJson('/api/products/subclases/reorder', [
                'ids' => [$c->id, $a->id, $b->id],
            ])
            ->assertOk();

        $this->assertEquals(1, ProductoSubclase::find($c->id)->orden);
        $this->assertEquals(2, ProductoSubclase::find($a->id)->orden);
        $this->assertEquals(3, ProductoSubclase::find($b->id)->orden);
    }

    public function test_reorder_subclases_requires_permission(): void
    {
        $subclase = ProductoSubclase::factory()->create();
        $this->asVendedor()
            ->postJson('/api/products/subclases/reorder', ['ids' => [$subclase->id]])
            ->assertForbidden();
    }
}
