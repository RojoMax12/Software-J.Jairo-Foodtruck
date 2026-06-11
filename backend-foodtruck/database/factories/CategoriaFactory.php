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
            ['nombre_categoria' => 'Normales', 'descripcion_categoria' => 'Helados clásicos y tradicionales.'],
            ['nombre_categoria' => 'Premium', 'descripcion_categoria' => 'Helados con ingredientes de mayor calidad y recetas especiales.'],
            ['nombre_categoria' => 'Vegano', 'descripcion_categoria' => 'Helados sin ingredientes de origen animal.'],
            ['nombre_categoria' => 'Sin Azúcar', 'descripcion_categoria' => 'Helados reducidos o libres de azúcar agregada.'],
            ['nombre_categoria' => 'Sin Lactosa', 'descripcion_categoria' => 'Helados aptos para personas sensibles a la lactosa.'],
        ];

        return $this->faker->randomElement($categorias);
    }
}
