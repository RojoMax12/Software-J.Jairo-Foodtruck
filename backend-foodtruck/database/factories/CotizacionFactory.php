<?php

namespace Database\Factories;

use App\Models\Cotizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cotizacion>
 */
class CotizacionFactory extends Factory
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
                'id_distribuidor' => \App\Models\Usuario_distribuidores::inRandomOrder()->first()?->id 
                                ?? \App\Models\Usuario_distribuidores::factory(),
                'id_estado_cotizacion' =>\App\Models\Estado_cotizacion::inRandomOrder()->first()?->id  ,
                'fecha_creacion' => now(),
                'hora_creacion' => now()->format('H:i'),
                'total_cotizacion' => $this->faker->numberBetween(1000, 10000),  
                'persona_recibe' => $this->faker->name(),
                'subtotal_cotizacion' => 0,
                'tipo_descuento_general' => 'none',
                'valor_descuento_general' => 0,
                'descuento_general_aplicado' => 0,
                'descuento_productos_total' => 0

            //
        ];
    }
}
