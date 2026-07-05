<?php

namespace Database\Factories;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
         'id_pedido' => $this->faker->numberBetween(1, 10),
         'id_caja' => $this->faker->numberBetween(1, 10),
            //
        ];
    }
}
