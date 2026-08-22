<?php

namespace Database\Factories;

use App\Models\EstadoPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstadoPago>
 */
class EstadoPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $estado_pago = [
            ['nombre_estado_pago' => 'Pendiente', 'descripcion_estado_pago' => 'El pago está pendiente de ser realizado.'],
            ['nombre_estado_pago' => 'Pagado', 'descripcion_estado_pago' => 'El pago ha sido realizado correctamente.'],
            ['nombre_estado_pago' => 'Rechazado', 'descripcion_estado_pago' => 'El pago ha sido rechazado por el sistema o el proveedor de pagos.'],
        ];

        return $estado_pago[$this->faker->numberBetween(0, count($estado_pago) - 1)];
    }
}
