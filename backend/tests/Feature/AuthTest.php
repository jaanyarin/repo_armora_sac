<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        User::create([
            'codigo' => 'ADMIN-001',
            'username' => 'admin',
            'name' => 'Admin',
            'nombre_completo' => 'Admin Test',
            'email' => 'admin@test.com',
            'dni' => '12345678',
            'password' => bcrypt('admin123'),
            'activo' => true,
        ])->assignRole('Super-Admin');
    }

    public function test_login_with_username(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_with_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'admin@test.com',
            'password' => 'admin123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_with_dni(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => '12345678',
            'password' => 'admin123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
    }

    public function test_me_endpoint(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('user.username', 'admin');
    }

    public function test_logout(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk();
    }
}
