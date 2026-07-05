<?php

namespace Database\Factories;

use App\Models\Detalle_Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Detalle_Pedido>
 */
class Detalle_pedidoFactory extends Factory
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
            'id_pedido' => $this->faker->numberBetween(1, 10),
            'id_tamaño' => $this->faker->numberBetween(1, 10),
            'cantidad' => $this->faker->numberBetween(1, 5),
            'precio_unitario' => $this->faker->randomFloat(2, 1, 100),
            //  
        ];
    }
}
