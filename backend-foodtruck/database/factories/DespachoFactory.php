<?php

namespace Database\Factories;

use App\Models\Despacho;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Despacho>
 */
class DespachoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'id_pedido' => \App\Models\Pedido::inRandomOrder()->first()?->id 
                            ?? \App\Models\Pedido::factory(),
            'fecha_entrega' => now()->addDays(rand(1, 14)), // Fecha de entrega entre 1 y 14 días a partir de hoy
            'direccion_entrega' => $this->faker->address(),
            'comuna' => $this->faker->city(),
            'persona_recibe' => $this->faker->name(),
            'estado_despacho' => $this->faker->randomElement(['Pendiente']),
        ];
    }
}
