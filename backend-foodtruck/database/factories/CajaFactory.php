<?php

namespace Database\Factories;

use App\Models\Caja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_usuario' => $this->faker->numberBetween(1, 10),
            'fecha_apertura' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'monto_inicial' => $this->faker->randomFloat(2, 100, 1000),
            'total_ventas' => $this->faker->numberBetween(0, 100),
            'total_recaudado' => $this->faker->randomFloat(2, 100, 10000),
        
            //
        ];
    }
}
