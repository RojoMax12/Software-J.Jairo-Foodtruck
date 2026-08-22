<?php

namespace Database\Factories;

use App\Models\Estado_pago;
use App\Models\Estado_pedido;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::inRandomOrder()->first()?->getKey() ?? Usuario::factory(),
            'id_estado_pedido' => Estado_pedido::inRandomOrder()->first()?->getKey() ?? Estado_pedido::factory(),
            'id_estado_pago' => Estado_pago::inRandomOrder()->first()?->getKey() ?? Estado_pago::factory(),
            'numero_pedido_dia' => $this->faker->numberBetween(1, 100),
            'nombre_persona' => $this->faker->name(),
            'numero_telefono' => $this->faker->phoneNumber(),
            'metodo_pago' => $this->faker->randomElement(['efectivo', 'tarjeta', 'transferencia']),
            'fecha_de_pago' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'total' => $this->faker->numberBetween(1000, 100000),
            'notas' => $this->faker->sentence(),
        ];
    }
}
