<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Company\Models\EmpresaConfig;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $vendedorToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        EmpresaConfig::firstOrCreate(['id' => 1]);

        $admin = User::factory()->create(['username' => 'admin']);
        $admin->assignRole('Admin');
        $this->adminToken = $admin->createToken('test')->plainTextToken;

        $vendedor = User::factory()->create(['username' => 'vendedor']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;
    }

    public function test_admin_can_view_company_config(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/empresa');

        $response->assertOk()
            ->assertJsonStructure([
                'razon_social',
                'porcentaje_igv',
                'ventas_bloqueadas',
                'compras_bloqueadas',
            ]);
    }

    public function test_vendedor_cannot_view_company_config(): void
    {
        $response = $this->withToken($this->vendedorToken)->getJson('/api/empresa');

        $response->assertForbidden();
    }

    public function test_admin_can_update_company_config(): void
    {
        $response = $this->withToken($this->adminToken)->putJson('/api/empresa', [
            'razon_social' => 'ARMORA SAC',
            'ruc' => '20123456789',
            'porcentaje_igv' => 18.00,
        ]);

        $response->assertOk()
            ->assertJson([
                'razon_social' => 'ARMORA SAC',
                'ruc' => '20123456789',
                'porcentaje_igv' => 18.00,
            ]);
    }

    public function test_actualizar_decimales_sunat(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/empresa/actualizar-decimales');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'products_actualizados',
                'stocks_actualizados',
            ]);
    }

    public function test_ventas_bloqueadas_flag_blocks_sale_creation(): void
    {
        $this->withToken($this->adminToken)->putJson('/api/empresa', [
            'ventas_bloqueadas' => true,
        ])->assertOk();

        $this->assertTrue(EmpresaConfig::first()->ventas_bloqueadas);
    }

    public function test_compras_bloqueadas_flag_blocks_purchase_creation(): void
    {
        $this->withToken($this->adminToken)->putJson('/api/empresa', [
            'compras_bloqueadas' => true,
        ])->assertOk();

        $this->assertTrue(EmpresaConfig::first()->compras_bloqueadas);
    }

    public function test_empresa_section_requires_mandatory_fields(): void
    {
        $response = $this->withToken($this->adminToken)->putJson('/api/empresa', [
            '__seccion' => 'empresa',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'razon_social',
                'ruc',
                'email',
                'telefono_celular',
                'departamento_id',
                'provincia_id',
                'ubigeo_id',
                'direccion',
            ]);
    }

    public function test_empresa_section_validates_ruc_format(): void
    {
        $response = $this->withToken($this->adminToken)->putJson('/api/empresa', [
            '__seccion' => 'empresa',
            'razon_social' => 'ARMORA SAC',
            'ruc' => '123',
            'email' => 'a@b.c',
            'telefono_celular' => '999888777',
            'departamento_id' => 15,
            'provincia_id' => 37,
            'ubigeo_id' => 1,
            'direccion' => 'Av 1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);
    }

    public function test_parametros_section_allows_partial_update(): void
    {
        $response = $this->withToken($this->adminToken)->putJson('/api/empresa', [
            'porcentaje_igv' => 19.00,
        ]);

        $response->assertOk()
            ->assertJson(['porcentaje_igv' => '19.00']);
    }
}
