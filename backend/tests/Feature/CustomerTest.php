<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
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
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_list_customers(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/customers');

        $response->assertOk();
    }

    public function test_create_customer(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
                'email' => 'cliente@test.com',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.nombre_completo', 'Cliente Test');
    }

    public function test_create_customer_with_duplicate_document(): void
    {
        $this->withHeaders($this->authHeaders())
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Duplicado',
            ]);

        $response->assertUnprocessable();
    }

    public function test_show_customer(): void
    {
        $create = $this->withHeaders($this->authHeaders())
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/customers/{$id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $id);
    }

    public function test_update_customer(): void
    {
        $create = $this->withHeaders($this->authHeaders())
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $id = $create->json('data.id');

        $response = $this->withHeaders($this->authHeaders())
            ->putJson("/api/customers/{$id}", [
                'nombre_completo' => 'Cliente Actualizado',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.nombre_completo', 'Cliente Actualizado');
    }

    public function test_delete_customer(): void
    {
        $create = $this->withHeaders($this->authHeaders())
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $id = $create->json('data.id');

        $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/customers/{$id}")
            ->assertOk();

        $this->withHeaders($this->authHeaders())
            ->getJson("/api/customers/{$id}")
            ->assertNotFound();
    }
}
