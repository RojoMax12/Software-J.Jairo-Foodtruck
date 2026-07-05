<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categorias = [
            ['nombre_categoria' => 'Papas & Chorillanas', 'descripcion_categoria' => 'Deliciosos productos con papas y chorillanas para disfrutar en cualquier momento.'],
            ['nombre_categoria' => 'Vianesas', 'descripcion_categoria' => 'Deliciosos productos con vianesas para disfrutar en cualquier momento.'],
            ['nombre_categoria' => 'Sanguches / Bajones', 'descripcion_categoria' => 'Sanguches y bajones para disfrutar en cualquier momento.'],
            ['nombre_categoria' => 'Promos / Combos', 'descripcion_categoria' => 'Promociones y combos especiales'],
            ['nombre_categoria' => 'Masas', 'descripcion_categoria' => 'Productos con pan'],
            ['nombre_categoria' => 'Bebestibles', 'descripcion_categoria' => 'Bebidas'], ];

        return $this->faker->randomElement($categorias);
    }
}
