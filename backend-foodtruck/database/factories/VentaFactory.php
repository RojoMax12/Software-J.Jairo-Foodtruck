<?php

namespace Database\Factories;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cotizacion' => \App\Models\Cotizacion::inRandomOrder()->first()?->id 
                            ?? \App\Models\Cotizacion::factory(),
            'numero_factura' => $this->faker->unique()->numerify('FAC-#####'),
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'glosa' => $this->faker->sentence(),
            'estado_pago' => $this->faker->randomElement(['Pendiente']),
            'monto_total' => $this->faker->numberBetween(10000, 1000000), 
            //
        ];
    }
}
