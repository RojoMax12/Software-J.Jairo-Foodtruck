<?php

namespace Database\Factories;

use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lote>
 */
class LoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $producto = \App\Models\Producto::inRandomOrder()->first()?->id
            ?? \App\Models\Producto::factory();

        $stock = \App\Models\Stock::inRandomOrder()->first()?->id
            ?? \App\Models\Stock::factory();

        $bodega = \App\Models\Bodega::inRandomOrder()->first()?->id
            ?? \App\Models\Bodega::factory();

        return [
            'id_producto' => $producto,
            'id_stock' => $stock,
            'id_bodega' => $bodega,
            'cantidad_producto' => $this->faker->numberBetween(20, 400),
            'fecha_emision' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}
