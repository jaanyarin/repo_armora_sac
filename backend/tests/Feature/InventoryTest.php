<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private string $jefeAlmacenToken;

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
    }

    private function asJefeAlmacen(): static
    {
        return $this->withToken($this->jefeAlmacenToken);
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
}
