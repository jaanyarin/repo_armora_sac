<?php

namespace Database\Factories\Modules\Products\Models;

use App\Modules\Products\Models\ProductoClase;
use App\Modules\Products\Models\ProductoSubclase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductoSubclase>
 */
class ProductoSubclaseFactory extends Factory
{
    protected $model = ProductoSubclase::class;

    public function definition(): array
    {
        $nombre = fake()->unique()->words(2, true);

        return [
            'clase_id' => ProductoClase::factory(),
            'codigo' => 'SCL-' . str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'nombre' => strtoupper($nombre),
            'slug' => Str::slug($nombre) . '-' . Str::random(4),
            'descripcion' => fake()->boolean(30) ? fake()->sentence() : null,
            'orden' => fake()->numberBetween(1, 200),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['activo' => false]);
    }
}
