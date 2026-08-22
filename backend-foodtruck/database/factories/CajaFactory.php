<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::inRandomOrder()->first()?->getKey() ?? Usuario::factory(),
            'fecha_apertura' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'monto_inicial' => $this->faker->numberBetween(100, 1000),
            'total_ventas' => $this->faker->numberBetween(0, 100),
            'total_recaudado' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
