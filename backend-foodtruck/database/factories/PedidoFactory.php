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

        'id_usuario_dicreme' => \App\Models\Usuario_dicreme::inRandomOrder()->first()?->id 
                                ?? \App\Models\Usuario_dicreme::factory(),

        'id_usuario_distribuidor' => \App\Models\Usuario_distribuidores::inRandomOrder()->first()?->id
                                ?? \App\Models\Usuario_distribuidores::factory(),

        'id_cotizacion' => \App\Models\Cotizacion::inRandomOrder()->first()?->id
                                ?? \App\Models\Cotizacion::factory(),
        // Busca el estado 'Validacion' (Asegúrate de crearlo en el Seeder antes)
        'id_estado_pedido' => \App\Models\Estado_pedido::where('nombre_estado', 'Validacion')->first()?->id,

        'fecha_creacion' => now(), 
        'hora_creacion' => now()->format('H:i'),
        'monto_estimado' => $this->faker->numberBetween(1000, 10000),
        'monto_final' => $this->faker->numberBetween(1000, 10000), // Por defecto, el monto final es igual al estimado, pero puedes ajustarlo según tus necesidades
        ];
    }
}
