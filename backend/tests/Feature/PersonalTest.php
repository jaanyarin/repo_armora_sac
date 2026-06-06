<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Personal\Models\Personal;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonalTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $vendedorToken;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::factory()->create(['username' => 'admin']);
        $this->admin->assignRole('Admin');
        $this->adminToken = $this->admin->createToken('test')->plainTextToken;

        $vendedor = User::factory()->create(['username' => 'vendedor']);
        $vendedor->assignRole('Vendedor');
        $this->vendedorToken = $vendedor->createToken('test')->plainTextToken;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'jperez',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'López',
            'nombres' => 'Juan',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'email' => 'jperez@test.com',
            'documento_identidad_id' => 1,
            'numero_documento' => '12345678',
        ], $overrides);
    }

    public function test_admin_can_list_personal(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/personal');
        $response->assertOk();
    }

    public function test_vendedor_cannot_list_personal(): void
    {
        $response = $this->withToken($this->vendedorToken)->getJson('/api/personal');
        $response->assertForbidden();
    }

    public function test_admin_can_create_personal(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.username', 'jperez')
            ->assertJsonPath('data.nombre_completo', 'Pérez López Juan')
            ->assertJsonPath('data.numero_documento', '12345678')
            ->assertJsonPath('data.documento_identidad_id', 1)
            ->assertJsonPath('data.activo', true);
    }

    public function test_create_personal_validates_required_fields(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/personal', [
            'username' => 'ab',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'apellido_paterno', 'apellido_materno', 'nombres', 'password']);
    }

    public function test_create_personal_validates_password_confirmation(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'password' => 'secreto123',
            'password_confirmation' => 'diferente999',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_create_personal_validates_unique_username(): void
    {
        $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload())->assertCreated();

        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'email' => 'otro@test.com',
            'numero_documento' => '87654321',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_create_personal_validates_dni_format(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'numero_documento' => '123',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['numero_documento']);
    }

    public function test_create_personal_validates_ruc_format(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'documento_identidad_id' => 4,
            'numero_documento' => '12345',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['numero_documento']);
    }

    public function test_create_personal_with_roles_and_permissions(): void
    {
        $permisoId = \Spatie\Permission\Models\Permission::where('name', 'ver-clientes')->first()->id;

        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'roles' => ['Vendedor'],
            'permisos' => [$permisoId],
        ]));

        $response->assertCreated();

        $personalId = $response->json('data.id');
        $personal = Personal::find($personalId);

        $this->assertTrue($personal->hasRole('Vendedor'));
        $this->assertTrue($personal->hasPermissionTo('ver-clientes'));
    }

    public function test_create_personal_with_listas_precios_and_almacenes(): void
    {
        $almacen = \App\Models\Catalog\Almacen::firstOrCreate(
            ['codigo' => 'TEST-001'],
            ['nombre' => 'Almacén Test', 'principal' => true, 'activo' => true]
        );

        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'listas_precios' => [1, 2],
            'almacenes' => [$almacen->id],
        ]));

        $response->assertCreated();

        $personalId = $response->json('data.id');
        $personal = Personal::find($personalId);

        $this->assertCount(2, $personal->listasPrecios);
        $this->assertCount(1, $personal->almacenes);
    }

    public function test_show_personal(): void
    {
        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $response = $this->withToken($this->adminToken)->getJson("/api/personal/{$id}");
        $response->assertOk()
            ->assertJsonPath('data.id', $id);
    }

    public function test_update_personal(): void
    {
        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $response = $this->withToken($this->adminToken)->putJson("/api/personal/{$id}", [
            'nombres' => 'Juan Carlos',
            'telefono_celular' => '987654321',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre_completo', 'Pérez López Juan Carlos')
            ->assertJsonPath('data.telefono_celular', '987654321');
    }

    public function test_update_personal_can_change_password(): void
    {
        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $response = $this->withToken($this->adminToken)->putJson("/api/personal/{$id}", [
            'password' => 'nuevaclave123',
            'password_confirmation' => 'nuevaclave123',
        ]);

        $response->assertOk();

        $personal = Personal::find($id);
        $this->assertTrue(\Hash::check('nuevaclave123', $personal->password));
    }

    public function test_delete_personal_soft_delete(): void
    {
        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $this->withToken($this->adminToken)->deleteJson("/api/personal/{$id}")->assertOk();

        $this->assertSoftDeleted('users', ['id' => $id]);
    }

    public function test_cannot_delete_self(): void
    {
        $response = $this->withToken($this->adminToken)->deleteJson("/api/personal/{$this->admin->id}");
        $response->assertForbidden();
    }

    public function test_vendedor_cannot_create_personal(): void
    {
        $response = $this->withToken($this->vendedorToken)->postJson('/api/personal', $this->payload());
        $response->assertForbidden();
    }

    public function test_upload_photo(): void
    {
        Storage::fake('public');

        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $file = UploadedFile::fake()->image('foto.jpg', 450, 150);

        $response = $this->withToken($this->adminToken)
            ->postJson("/api/personal/{$id}/foto", ['foto' => $file]);

        $response->assertOk()
            ->assertJsonPath('data.foto_path', fn($path) => str_starts_with($path, "personal/{$id}/"));

        $personal = Personal::find($id);
        $this->assertNotNull($personal->foto_path);
        Storage::disk('public')->assertExists($personal->foto_path);
    }

    public function test_upload_photo_validates_file(): void
    {
        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $response = $this->withToken($this->adminToken)
            ->postJson("/api/personal/{$id}/foto", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['foto']);
    }

    public function test_reset_photo(): void
    {
        Storage::fake('public');

        $create = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload());
        $id = $create->json('data.id');

        $file = UploadedFile::fake()->image('foto.jpg');
        $this->withToken($this->adminToken)->postJson("/api/personal/{$id}/foto", ['foto' => $file])->assertOk();

        $this->withToken($this->adminToken)->deleteJson("/api/personal/{$id}/foto")->assertOk();

        $personal = Personal::find($id);
        $this->assertNull($personal->foto_path);
    }

    public function test_roles_disponibles(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/personal/roles-disponibles');
        $response->assertOk()
            ->assertJsonStructure([
                ['id', 'name', 'guard_name'],
            ]);
    }

    public function test_permisos_agrupados(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/personal/permisos-agrupados');
        $response->assertOk()
            ->assertJsonStructure([
                ['modulo', 'permisos' => [['id', 'name', 'descripcion']]],
            ]);
    }

    public function test_list_personal_with_search(): void
    {
        $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'username' => 'buscable',
            'apellido_paterno' => 'Buscable',
            'apellido_materno' => 'Apellido',
            'nombres' => 'Persona',
        ]))->assertCreated();

        $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'username' => 'otrousr',
            'apellido_paterno' => 'Otro',
            'apellido_materno' => 'Usuario',
            'nombres' => 'Distinto',
            'email' => 'otro@test.com',
            'numero_documento' => '87654321',
        ]))->assertCreated();

        $response = $this->withToken($this->adminToken)->getJson('/api/personal?search=buscable');
        $response->assertOk()
            ->assertJsonPath('data.0.username', 'buscable');
    }

    public function test_create_personal_allows_only_one_role(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/personal', $this->payload([
            'roles' => ['Vendedor', 'Almacenero'],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);
    }
}
