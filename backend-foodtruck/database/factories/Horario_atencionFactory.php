<?php

namespace Database\Factories;

use App\Models\Horario_atencion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario_atencion>
 */
class Horario_atencionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dia_semana' => $this->faker->randomElement(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']),
            'hora_apertura' => $this->faker->time('H:i:s'),
            'hora_cierre' => $this->faker->time('H:i:s'),
            'minuto_colchon' => $this->faker->numberBetween(0, 60),
            'activo' => $this->faker->boolean(),
            'id_usuario' => $this->faker->numberBetween(1, 10),
        ];
    }
}
