<?php

namespace Database\Factories;

use App\Models\OfertaProducto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfertaProducto>
 */
class OfertaProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_producto' => $this->faker->numberBetween(1, 10),
            'id_oferta' => $this->faker->numberBetween(1, 10),
            //
        ];
    }
}
