<?php

namespace Database\Factories\Modules\Products\Models;

use App\Modules\Products\Models\ProductoClase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductoClase>
 */
class ProductoClaseFactory extends Factory
{
    protected $model = ProductoClase::class;

    public function definition(): array
    {
        $nombre = fake()->unique()->company();

        return [
            'codigo' => 'CLS-' . str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'nombre' => strtoupper($nombre),
            'slug' => Str::slug($nombre) . '-' . Str::random(4),
            'descripcion' => fake()->boolean(40) ? fake()->sentence() : null,
            'licor' => fake()->boolean(15),
            'orden' => fake()->numberBetween(1, 200),
            'activo' => true,
        ];
    }

    public function licor(): static
    {
        return $this->state(fn() => ['licor' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['activo' => false]);
    }
}
