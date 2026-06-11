<?php

namespace Database\Factories;

use App\Models\Cotizacion_producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cotizacion_producto>
 */
class Cotizacion_productoFactory extends Factory
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
            'id_producto' => \App\Models\Producto::inRandomOrder()->first()?->id 
                            ?? \App\Models\Producto::factory(),
            'cantidad' => $this->faker->numberBetween(1, 10),
            'precio_unitario_venta' => $this->faker->numberBetween(1000, 100000), 
        ];
    }
}
