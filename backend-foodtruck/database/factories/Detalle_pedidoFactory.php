<?php

namespace Database\Factories;

use App\Models\Detalle_Pedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Tamaño;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Detalle_Pedido>
 */
class Detalle_pedidoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_producto' => Producto::inRandomOrder()->first()?->getKey() ?? Producto::factory(),
            'id_pedido' => Pedido::inRandomOrder()->first()?->getKey() ?? Pedido::factory(),
            'id_tamaño' => Tamaño::inRandomOrder()->first()?->getKey() ?? Tamaño::factory(),
            'cantidad' => $this->faker->numberBetween(1, 5),
            'precio_unitario' => $this->faker->numberBetween(100, 1000),
        ];
    }
}
