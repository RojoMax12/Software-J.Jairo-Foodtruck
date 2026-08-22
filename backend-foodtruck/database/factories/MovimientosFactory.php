<?php

namespace Database\Factories;

use App\Models\Ingrediente;
use App\Models\Movimientos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movimientos>
 */
class MovimientosFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_ingrediente' => Ingrediente::inRandomOrder()->first()?->getKey() ?? Ingrediente::factory(),
            'tipo_movimiento' => $this->faker->randomElement(['entrada', 'salida']),
            'cantidad' => $this->faker->numberBetween(1, 50),
            'fecha_movimiento' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
