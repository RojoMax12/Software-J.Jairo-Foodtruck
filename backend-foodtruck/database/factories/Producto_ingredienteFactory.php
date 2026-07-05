<?php

namespace Database\Factories;

use App\Models\Producto_ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto_ingrediente>
 */
class Producto_ingredienteFactory extends Factory
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
            'id_ingrediente' => $this->faker->numberBetween(1, 10),
            'incluido_por_defecto' => $this->faker->boolean(),
            //
        ];
    }
}
