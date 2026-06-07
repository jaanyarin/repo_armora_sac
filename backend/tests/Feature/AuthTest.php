<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private string $password = 'admin123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        User::factory()->create([
            'codigo' => 'ADMIN-001',
            'username' => 'admin',
            'name' => 'Admin',
            'nombre_completo' => 'Admin Test',
            'email' => 'admin@test.com',
            'documento_identidad_id' => 1,
            'numero_documento' => '12345678',
            'password' => bcrypt($this->password),
            'activo' => true,
        ])->assignRole('Super-Admin');
    }

    public function test_login_with_username(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'admin',
            'password' => $this->password,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_with_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'admin@test.com',
            'password' => $this->password,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_with_dni(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => '12345678',
            'password' => $this->password,
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
            'password' => $this->password,
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
            'password' => $this->password,
        ]);

        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk();
    }

    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        $payload = ['login' => 'admin', 'password' => 'wrong'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', $payload)
                ->assertUnprocessable();
        }

        $this->postJson('/api/auth/login', $payload)
            ->assertStatus(429)
            ->assertJsonStructure(['message']);
    }

    /**
     * A-06: DNI con longitud inválida (no 8 dígitos) → 422.
     */
    public function test_dni_with_invalid_length_is_rejected(): void
    {
        $this->postJson('/api/auth/login', [
            'login' => '1234', // numérico pero no 8 ni 11 dígitos
            'password' => $this->password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }

    /**
     * A-06: RUC con longitud inválida (no 11 dígitos) → 422.
     */
    public function test_ruc_with_invalid_length_is_rejected(): void
    {
        $this->postJson('/api/auth/login', [
            'login' => '1234567890', // 10 dígitos, no es RUC válido
            'password' => $this->password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }

    /**
     * A-06: DNI válido (8 dígitos) con password incorrecto → 422 con errors.login
     * (no 422 por errors.password). Esto valida que la validación de FORMATO pasa
     * y el rechazo es por autenticación, no por regex.
     */
    public function test_valid_dni_format_passes_format_validation(): void
    {
        $this->postJson('/api/auth/login', [
            'login' => '12345678', // DNI válido
            'password' => 'wrong_password_largo',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }
}
