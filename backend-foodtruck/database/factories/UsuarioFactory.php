<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rol' => $this->faker->numberBetween(1, 10),
            'nombre' => $this->faker->name(),
            'estado' => $this->faker->boolean(),
            'contrasena' => $this->faker->password(),
            'correo' => $this->faker->unique()->safeEmail(),
        ];
    }
}
