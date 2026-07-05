<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Producto_Tamaño;
use App\Models\Tamaño;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto_Tamaño>
 */
class Producto_TamañoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_producto' => Producto::inRandomOrder()->first()?->getKey() ?? Producto::factory(),
            'id_tamaño' => Tamaño::inRandomOrder()->first()?->getKey() ?? Tamaño::factory(),
            'precio' => $this->faker->numberBetween(1000, 10000),
        ];
    }
}
