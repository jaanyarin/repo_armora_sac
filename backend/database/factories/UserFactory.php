<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'codigo' => 'USR-' . fake()->unique()->randomNumber(5),
            'username' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'nombre_completo' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'telefono' => fake()->phoneNumber(),
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'codigo' => 'ADMIN-001',
            'username' => 'admin',
            'name' => 'Admin',
            'nombre_completo' => 'Administrador del Sistema',
            'email' => 'admin@armorasac.com',
            'documento_identidad_id' => 1,
            'numero_documento' => '12345678',
            'password' => Hash::make('admin123'),
        ]);
    }

    public function vendedor(): static
    {
        return $this->state(fn(array $attributes) => [
            'codigo' => 'VEND-001',
            'username' => 'vendedor',
            'name' => 'Vendedor',
            'nombre_completo' => 'Vendedor Demo',
            'email' => 'vendedor@armorasac.com',
            'documento_identidad_id' => 1,
            'numero_documento' => '87654321',
            'password' => Hash::make('vendedor123'),
        ]);
    }
}
