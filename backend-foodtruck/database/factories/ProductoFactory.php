<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(2, true),
            'descripcion' => $this->faker->sentence(),
            'tipo_armado' => $this->faker->randomElement(['predeterminado', 'personalizado']),
            'cantidad_incluida' => $this->faker->numberBetween(1, 5),
            'precio_ingrediente_extra' => $this->faker->numberBetween(1000, 5000),
            'id_categoria' => Categoria::inRandomOrder()->first()?->getKey() ?? Categoria::factory(),
        ];
    }
}
