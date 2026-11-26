<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\CheckRole;
use App\Models\Usuario;
use Tests\TestCase;

class CashAndUserSecurityTest extends TestCase
{
    public function test_register_validates_telefono_length_and_symbol_password_rule(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nombre' => 'Cliente Test',
            'correo' => 'cliente_test@example.com',
            'telefono' => str_repeat('9', 45),
            'contrasena' => 'SinSimbolo123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['telefono', 'contrasena']);
    }

    public function test_usuario_store_rejects_invalid_role_and_invalid_phone(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        $response = $this->postJson('/api/usuarios', [
            'nombre' => 'Trabajador Fake',
            'telefono' => '123',
            'correo' => 'correo-invalido',
            'id_rol' => 99,
            'contrasena' => 'Valida#1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['telefono', 'correo', 'id_rol']);
    }

    public function test_caja_destroy_denies_worker_role(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class]);

        $worker = new Usuario(['id_usuario' => 5, 'id_rol' => 3, 'nombre' => 'Cocina']);
        $this->actingAs($worker);

        $this->deleteJson('/api/cajas/1')->assertStatus(403);
    }

    public function test_detalle_pedido_store_requires_valid_product_and_order(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        $response = $this->postJson('/api/detalle_pedidos', [
            'id_pedido' => 'invalid',
            'id_producto' => 'invalid',
            'cantidad' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_pedido', 'id_producto', 'cantidad']);
    }
}
