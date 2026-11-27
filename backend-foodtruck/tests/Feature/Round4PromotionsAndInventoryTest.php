<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\CheckRole;
use Tests\TestCase;

class Round4PromotionsAndInventoryTest extends TestCase
{
    public function test_promotions_batch_validates_items_array_and_required_fields(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        $this->postJson('/api/promociones/batch', ['items' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        $this->postJson('/api/promociones/batch', [
            'items' => [
                [
                    'id_producto' => 'invalid',
                    'titulo' => '',
                    'precio_promocional' => 0,
                    'fecha_inicio' => '2026-11-26',
                    'fecha_fin' => '2026-11-20',
                ],
            ],
        ])->assertStatus(422)
          ->assertJsonValidationErrors([
              'items.0.id_producto',
              'items.0.titulo',
              'items.0.precio_promocional',
              'items.0.fecha_fin',
          ]);
    }

    public function test_ingrediente_store_validates_tipo_inventario_and_unidades_por_pack(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        $this->postJson('/api/ingredientes', [
            'nombre' => 'Pan Pack Test',
            'tipo_inventario' => 'invalido',
            'unidades_por_pack' => 0,
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['tipo_inventario', 'unidades_por_pack']);
    }
}

