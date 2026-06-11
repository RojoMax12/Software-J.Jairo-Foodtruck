<?php

namespace Database\Factories;

use App\Models\Usuario_dicreme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario_dicreme>
 */
class Usuario_dicremeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_usuario' => $this->faker->userName(),
            'correo_electronico' => $this->faker->unique()->safeEmail(),
            'contrasena' => 'password',
            // Selecciona un ID de la tabla de roles que ya fue poblada
            // SOLUCIÓN: Si existe un rol, toma su ID. Si no, crea uno nuevo.
            'id_rol' => \App\Models\Rol::whereIn('nombre_rol', ['Admin', 'Trabajador'])
            ->inRandomOrder()
            ->first()?->id
            ?? \App\Models\Rol::factory(),
            ];

    }
}