<?php

namespace Database\Factories;

use App\Models\Ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingrediente>
 */
class IngredienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'cantidad_actual' => $this->faker->numberBetween(1, 100),
            'cantidad_minima' => $this->faker->numberBetween(1, 50),
            'fecha_de_ingreso' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'disponible' => $this->faker->boolean(),
            //
        ];
    }
}
