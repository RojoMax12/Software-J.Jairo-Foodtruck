<?php

namespace Database\Factories;

use App\Models\Bodega;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bodega>
 */
class BodegaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombres = [
            'Bodega Central de Helados',
            'Bodega Norte',
            'Bodega Sur',
            'Bodega Frutilla',
            'Bodega Vainilla',
        ];

        $ubicaciones = [
            'Santiago Centro',
            'Providencia',
            'Maipú',
            'Ñuñoa',
            'La Florida',
        ];

        return [
            'nombre_bodega' => $this->faker->randomElement($nombres),
            'ubicacion_bodega' => $this->faker->randomElement($ubicaciones),
            'cantidad_productos' => $this->faker->numberBetween(10, 250),
        ];
    }
}
