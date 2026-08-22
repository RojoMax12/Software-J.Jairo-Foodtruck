<?php

namespace Database\Factories;

use App\Models\Detalle_Pedido;
use App\Models\Ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Detalle_pedido_ingrediente>
 */
class Detalle_pedido_ingredienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_detalle_pedido' => Detalle_Pedido::inRandomOrder()->first()?->getKey() ?? Detalle_Pedido::factory(),
            'id_ingrediente' => Ingrediente::inRandomOrder()->first()?->getKey() ?? Ingrediente::factory(),
            'tipo_modificacion' => $this->faker->randomElement(['agregado', 'eliminado']),
            'precio_aplicado' => $this->faker->numberBetween(100, 1000),
        ];
    }
}
