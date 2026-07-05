<?php

namespace Database\Factories;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
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
        'id_estado_pedido' => $this->faker->numberBetween(1, 5),
        'id_estado_pago' => $this->faker->numberBetween(1, 3),
        'numero_pedido_dia' => $this->faker->numberBetween(1, 100),
        'nombre_persona' => $this->faker->name(),
        'numero_telefono' => $this->faker->phoneNumber(),
        'metodo_pago' => $this->faker->randomElement(['efectivo', 'tarjeta', 'transferencia']),
        'fecha_de_pago' => $this->faker->dateTimeBetween('-1 month', 'now'),
        'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
        'total' => $this->faker->randomFloat(2, 10, 500),
        'nota' => $this->faker->sentence(),];
    }
}
