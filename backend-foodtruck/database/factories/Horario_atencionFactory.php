<?php

namespace Database\Factories;

use App\Models\Horario_atencion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario_atencion>
 */
class Horario_atencionFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Se guarda como número (0=Domingo ... 6=Sábado), no como texto.
            'dia_semana' => $this->faker->numberBetween(0, 6),
            'hora_apertura' => $this->faker->time('H:i:s'),
            'hora_cierre' => $this->faker->time('H:i:s'),
            'minuto_colchon' => $this->faker->numberBetween(0, 60),
            'activo' => $this->faker->boolean(),
            'id_usuario' => Usuario::inRandomOrder()->first()?->getKey() ?? Usuario::factory(),
        ];
    }
}
