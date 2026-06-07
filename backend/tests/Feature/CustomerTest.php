<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create(['username' => 'admin']);
        $this->admin->assignRole('Super-Admin');
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin, 'sanctum');
    }

    public function test_list_customers(): void
    {
        $response = $this->asAdmin()
            ->getJson('/api/customers');

        $response->assertOk();
    }

    public function test_create_customer(): void
    {
        $response = $this->asAdmin()
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
                'email' => 'cliente@test.com',
            ]);

        $response->assertCreated()
            ->assertJsonPath('nombre_completo', 'Cliente Test');
    }

    public function test_create_customer_with_duplicate_document(): void
    {
        $this->asAdmin()
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $response = $this->asAdmin()
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Duplicado',
            ]);

        $response->assertUnprocessable();
    }

    public function test_show_customer(): void
    {
        $create = $this->asAdmin()
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $id = $create->json('id');

        $response = $this->asAdmin()
            ->getJson("/api/customers/{$id}");

        $response->assertOk()
            ->assertJsonPath('id', $id);
    }

    public function test_update_customer(): void
    {
        $create = $this->asAdmin()
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $id = $create->json('id');

        $response = $this->asAdmin()
            ->putJson("/api/customers/{$id}", [
                'nombre_completo' => 'Cliente Actualizado',
            ]);

        $response->assertOk()
            ->assertJsonPath('nombre_completo', 'Cliente Actualizado');
    }

    public function test_delete_customer(): void
    {
        $create = $this->asAdmin()
            ->postJson('/api/customers', [
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'nombre_completo' => 'Cliente Test',
            ]);

        $id = $create->json('id');

        $this->asAdmin()
            ->deleteJson("/api/customers/{$id}")
            ->assertOk();

        $this->asAdmin()
            ->getJson("/api/customers/{$id}")
            ->assertNotFound();
    }
}
