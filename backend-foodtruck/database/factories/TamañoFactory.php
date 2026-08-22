<?php

namespace Database\Factories;

use App\Models\Tamaño;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tamaño>
 */
class TamañoFactory extends Factory
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
        ];
    }
}
