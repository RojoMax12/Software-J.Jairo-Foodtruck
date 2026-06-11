<?php

namespace Database\Factories;

use App\Models\Usuario_distribuidores;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario_distribuidores>
 */
class Usuario_distribuidoresFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rol' => \App\Models\Rol::where('nombre_rol', 'Distribuidor')->first()?->id,
            'rut_empresa' => $this->faker->unique()->numerify('########-#'),
            'nombre_empresa' => $this->faker->company(),
            'contrasena' => 'password',
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'correo_electronico' => $this->faker->unique()->safeEmail(),
            'comuna' => $this->faker->city(),
        ];
    }
}
