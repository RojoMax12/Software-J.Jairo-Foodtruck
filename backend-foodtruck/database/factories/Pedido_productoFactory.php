<?php

namespace Database\Factories;

use App\Models\Pedido_producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido_producto>
 */
class Pedido_productoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pedido = \App\Models\Pedido::inRandomOrder()->first()?->id
            ?? \App\Models\Pedido::factory();

        $producto = \App\Models\Producto::inRandomOrder()->first()?->id
            ?? \App\Models\Producto::factory();

        return [
            'id_pedido' => $pedido,
            'id_producto' => $producto,
            'precio_unitario_venta' => $this->faker->numberBetween(2500, 18000),
            'cantidad' => $this->faker->numberBetween(1, 8),
        ];
    }
}
