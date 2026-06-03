<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $user = User::create([
            'codigo' => 'ADMIN-001',
            'username' => 'admin',
            'name' => 'Admin',
            'nombre_completo' => 'Admin Test',
            'email' => 'admin@test.com',
            'dni' => '12345678',
            'password' => bcrypt('admin123'),
            'activo' => true,
        ]);
        $user->assignRole('Super-Admin');

        $login = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $this->token = $login->json('token');

        // Seed dimension tables needed for products
        $this->seedDimTables();
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    private function seedDimTables(): void
    {
        \Illuminate\Support\Facades\DB::unprepared("
            INSERT INTO dim_unidad_medida (codigo_sunat, nombre, simbolo) VALUES ('NIU', 'UNIDADES', 'UND') ON CONFLICT (codigo_sunat) DO NOTHING;
            INSERT INTO dim_producto_clase (nombre) VALUES ('Producto Terminado') ON CONFLICT DO NOTHING;
            INSERT INTO dim_tipo_afeccion_igv (codigo_sunat, nombre, tributo_asociado) VALUES ('10', 'Gravado - Operación Onerosa', '1000') ON CONFLICT (codigo_sunat) DO NOTHING;
        ");
    }

    public function test_list_products(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/products');

        $response->assertOk();
    }

    public function test_create_product(): void
    {
        $umId = \Illuminate\Support\Facades\DB::table('dim_unidad_medida')->where('codigo_sunat', 'NIU')->first()->id;

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/products', [
                'nombre' => 'Producto Test',
                'unidad_medida_id' => $umId,
                'precio_venta' => 100.50,
                'stock_actual' => 50,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre', 'Producto Test');
    }

    public function test_create_product_requires_unidad_medida(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/products', [
                'nombre' => 'Producto Test',
            ]);

        $response->assertUnprocessable();
    }

    public function test_show_product(): void
    {
        $umId = \Illuminate\Support\Facades\DB::table('dim_unidad_medida')->where('codigo_sunat', 'NIU')->first()->id;

        $create = $this->withHeaders($this->authHeaders())
            ->postJson('/api/products', [
                'nombre' => 'Producto Test',
                'unidad_medida_id' => $umId,
            ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/products/{$id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $id);
    }

    public function test_update_product(): void
    {
        $umId = \Illuminate\Support\Facades\DB::table('dim_unidad_medida')->where('codigo_sunat', 'NIU')->first()->id;

        $create = $this->withHeaders($this->authHeaders())
            ->postJson('/api/products', [
                'nombre' => 'Producto Test',
                'unidad_medida_id' => $umId,
            ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($this->authHeaders())
            ->putJson("/api/products/{$id}", [
                'precio_venta' => 250.00,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.precio_venta', '250.00');
    }

    public function test_delete_product(): void
    {
        $umId = \Illuminate\Support\Facades\DB::table('dim_unidad_medida')->where('codigo_sunat', 'NIU')->first()->id;

        $create = $this->withHeaders($this->authHeaders())
            ->postJson('/api/products', [
                'nombre' => 'Producto Test',
                'unidad_medida_id' => $umId,
            ]);

        $id = $create->json('data.id');

        $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/products/{$id}")
            ->assertOk();

        $this->withHeaders($this->authHeaders())
            ->getJson("/api/products/{$id}")
            ->assertNotFound();
    }
}
