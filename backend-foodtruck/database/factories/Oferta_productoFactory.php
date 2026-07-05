<?php

namespace Database\Factories;

use App\Models\Oferta_producto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Oferta_producto>
 */
class Oferta_productoFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Antes: 'id_producto' (sin s) e 'id_oferta' -> ninguno coincide con las
            // columnas reales de oferta_producto ('id_productos', 'descripcion',
            // 'precio_oferta', 'tipo'), así que se insertaban todo NULL.
            'id_productos' => Producto::inRandomOrder()->first()?->getKey() ?? Producto::factory(),
            'descripcion' => $this->faker->sentence(),
            'precio_oferta' => $this->faker->numberBetween(1000, 10000),
            'tipo' => $this->faker->randomElement(['porcentaje', 'monto_fijo']),
        ];
    }
}
