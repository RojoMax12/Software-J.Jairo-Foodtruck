<?php

namespace Database\Factories;

use App\Models\Detalle_pedido_ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Detalle_pedido_ingrediente>
 */
class Detalle_pedido_ingredienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_detalle_pedido' => $this->faker->numberBetween(1, 10),
            'id_ingrediente' => $this->faker->numberBetween(1, 10),
            'tipo_modificacion' => $this->faker->randomElement(['agregado', 'eliminado']),
            'precio_aplicado' => $this->faker->randomFloat(2, 0, 10),
            //
        ];
    }
}
