<?php

namespace Database\Factories;

use App\Models\EstadoPedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstadoPedido>
 */
class EstadoPedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   
        $estado_pedido = [
            ['nombre_estado_pedido' => 'Pendiente', 'descripcion_estado_pedido' => 'El pedido está pendiente de ser procesado.'],
            ['nombre_estado_pedido' => 'En preparación', 'descripcion_estado_pedido' => 'El pedido está siendo preparado por el personal de cocina.'],
            ['nombre_estado_pedido' => 'Listo para entregar', 'descripcion_estado_pedido' => 'El pedido está listo para ser entregado al cliente.'],
            ['nombre_estado_pedido' => 'Entregado', 'descripcion_estado_pedido' => 'El pedido ha sido entregado al cliente.'],
            ['nombre_estado_pedido' => 'Cancelado', 'descripcion_estado_pedido' => 'El pedido ha sido cancelado por el cliente o por el personal del restaurante.'],
        ];
        
        return $estado_pedido[$this->faker->numberBetween(0, count($estado_pedido) - 1)];
    }
}
