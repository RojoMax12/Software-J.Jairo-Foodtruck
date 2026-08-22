<?php

namespace Database\Factories;

use App\Models\Oferta;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Oferta>
 */
class OfertaFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Antes faltaba por completo: id_productos es NOT NULL en la tabla.
            'id_productos' => Producto::inRandomOrder()->first()?->getKey() ?? Producto::factory(),
            'descripcion' => $this->faker->sentence(),
            'precio_oferta' => $this->faker->numberBetween(1000, 10000),
            // Antes faltaba: "tipo" es NOT NULL en la tabla.
            'tipo' => $this->faker->randomElement(['porcentaje', 'monto_fijo']),
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'dia_semana' => $this->faker->numberBetween(0, 6),
        ];
    }
}
