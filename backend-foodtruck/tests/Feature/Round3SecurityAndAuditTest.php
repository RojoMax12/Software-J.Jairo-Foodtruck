<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\CheckRole;
use App\Models\Usuario;
use Tests\TestCase;

class Round3SecurityAndAuditTest extends TestCase
{
    public function test_venta_store_validates_against_real_caja_table_without_cajas_sql_error(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        $response = $this->postJson('/api/ventas', [
            'id_pedido' => 'invalid',
            'id_caja' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_pedido', 'id_caja']);
    }

    public function test_historial_movimientos_blocks_update_and_delete_for_immutability(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        $this->putJson('/api/historial_movimientos/1', ['descripcion' => 'alterado'])
            ->assertStatus(403);

        $this->deleteJson('/api/historial_movimientos/1')
            ->assertStatus(403);
    }

    public function test_worker_cannot_delete_pedido_or_modify_kardex_or_payment_states(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class]);

        $worker = new Usuario(['id_usuario' => 7, 'id_rol' => 3, 'nombre' => 'Cocina']);
        $this->actingAs($worker);

        $this->deleteJson('/api/pedidos/1')->assertStatus(403);
        $this->postJson('/api/movimientos', [
            'id_ingrediente' => 1,
            'cantidad' => 10,
            'tipo_movimiento' => 'Entrada',
        ])->assertStatus(403);
        $this->deleteJson('/api/movimientos/1')->assertStatus(403);
        $this->deleteJson('/api/estado_pedidos/1')->assertStatus(403);
        $this->deleteJson('/api/estado_pagos/1')->assertStatus(403);
    }

    public function test_admin_cannot_delete_system_roles_or_core_statuses(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class]);

        $admin = new Usuario(['id_usuario' => 1, 'id_rol' => 1, 'nombre' => 'Admin']);
        $this->actingAs($admin);

        $this->deleteJson('/api/roles/1')->assertStatus(422);
        $this->deleteJson('/api/estado_pedidos/4')->assertStatus(422);
        $this->deleteJson('/api/estado_pagos/2')->assertStatus(422);
    }
}

