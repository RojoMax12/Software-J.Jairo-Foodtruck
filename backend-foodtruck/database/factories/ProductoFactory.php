<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'precio_ingrediente_extra' => $this->faker->randomFloat(2, 0, 10),
            'tipo_armado' => $this->faker->randomElement(['personalizado', 'predeterminado']),
            'cantidad_incluida' => $this->faker->numberBetween(1, 5),
            'id_categoria' => $this->faker->numberBetween(1, 10),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
