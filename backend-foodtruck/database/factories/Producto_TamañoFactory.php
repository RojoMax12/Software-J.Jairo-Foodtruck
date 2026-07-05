<?php

namespace Database\Factories;

use App\Models\Producto_Tamaño;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto_Tamaño>
 */
class Producto_TamañoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_producto' => $this->faker->numberBetween(1, 10),
            'id_tamaño' => $this->faker->numberBetween(1, 10),
            'precio' => $this->faker->randomFloat(2, 1, 100),
            //
        ];
    }
}
