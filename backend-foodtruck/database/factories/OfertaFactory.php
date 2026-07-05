<?php

namespace Database\Factories;

use App\Models\Oferta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Oferta>
 */
class OfertaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descripcion' => $this->faker->sentence(),
            'precio_oferta' => $this->faker->randomFloat(2, 1, 100),
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'dia_semana' => $this->faker->randomElement(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']),
        ];
    }
}
