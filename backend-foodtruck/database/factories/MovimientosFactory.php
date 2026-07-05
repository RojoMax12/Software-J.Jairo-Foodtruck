<?php

namespace Database\Factories;

use App\Models\Movimientos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movimientos>
 */
class MovimientosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_ingrediente' => $this->faker->numberBetween(1, 10),
            'tipo_movimiento' => $this->faker->randomElement(['entrada', 'salida']),
            'cantidad' => $this->faker->numberBetween(1, 50),
            'fecha_movimiento' => $this->faker->dateTimeBetween('-1 year', 'now'),
            //
        ];
    }
}
