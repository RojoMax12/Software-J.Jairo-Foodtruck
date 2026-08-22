<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_rol' => Rol::inRandomOrder()->first()?->getKey() ?? Rol::factory(),
            'nombre' => $this->faker->name(),
            'estado' => $this->faker->boolean(),
            'contrasena' => $this->faker->password(),
            'correo' => $this->faker->unique()->safeEmail(),
        ];
    }
}
