<?php

namespace Database\Factories;

use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Producto_ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto_ingrediente>
 */
class Producto_ingredienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_producto' =>  Producto::factory(),
            'id_ingrediente' =>  Ingrediente::factory(),
            'incluido_por_defecto' => $this->faker->boolean(),
        ];
    }
}
