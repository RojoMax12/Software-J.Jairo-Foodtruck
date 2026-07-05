<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\Pedido;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_pedido' => Pedido::inRandomOrder()->first()?->getKey() ?? Pedido::factory(),
            'id_caja' => Caja::inRandomOrder()->first()?->getKey() ?? Caja::factory(),
        ];
    }
}
