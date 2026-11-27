<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\CheckRole;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Producto_ingrediente;
use App\Repositories\PedidoRepository;
use Illuminate\Validation\PresenceVerifierInterface;
use Tests\TestCase;

class Round5AuditFixesTest extends TestCase
{
    public function test_promotions_batch_rejects_mixing_all_and_specific_size_for_same_product(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        // Evitar dependencia de SQLite para la regla exists:* en entorno sin pdo_sqlite
        $this->app->bind('validation.presence', fn () => new class implements PresenceVerifierInterface {
            public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = [])
            {
                return 1;
            }

            public function getMultiCount($collection, $column, array $values, array $extra = [])
            {
                return count($values);
            }
        });

        $this->postJson('/api/promociones/batch', [
            'reemplazar_conflictos' => true,
            'items' => [
                [
                    'id_producto' => 1,
                    'id_tamaño' => null,
                    'titulo' => 'Promo Todos',
                    'precio_promocional' => 1500,
                    'fecha_inicio' => '2026-11-26',
                    'fecha_fin' => '2026-11-28',
                    'activo' => true,
                ],
                [
                    'id_producto' => 1,
                    'id_tamaño' => 1,
                    'titulo' => 'Promo Normal',
                    'precio_promocional' => 1400,
                    'fecha_inicio' => '2026-11-26',
                    'fecha_fin' => '2026-11-28',
                    'activo' => true,
                ],
            ],
        ])->assertStatus(422)
          ->assertJsonPath('message', 'No puedes mezclar "Todos los tamaños" con tamaños específicos del mismo producto en la fila #2.');
    }

    public function test_pedido_repository_search_handles_non_numeric_safely_and_prioritizes_size_extra_price(): void
    {
        $repo = app(PedidoRepository::class);

        $this->assertNull($repo->getPedidoBySearch('no-es-numero'));

        $ing = new Ingrediente([
            'id_ingrediente' => 99,
            'nombre' => 'Queso cheddar',
            'disponible' => true,
        ]);
        $ing->id_ingrediente = 99;

        $piSize1 = new Producto_ingrediente([
            'id_ingrediente' => 99,
            'id_tamaño' => 1,
            'incluido_por_defecto' => false,
            'precio_extra' => 400,
        ]);
        $piSize1->setRelation('ingrediente', $ing);

        $piSize2 = new Producto_ingrediente([
            'id_ingrediente' => 99,
            'id_tamaño' => 2,
            'incluido_por_defecto' => false,
            'precio_extra' => 900,
        ]);
        $piSize2->setRelation('ingrediente', $ing);

        $producto = new Producto([
            'nombre' => 'Pizza Test',
            'cantidad_incluida' => 0,
            'precio_ingrediente_extra' => 500,
        ]);
        $producto->setRelation('ingredientes', collect([$piSize1, $piSize2]));

        $resSmall = $repo->calcularCostoExtras($producto, [['id_ingrediente' => 99, 'nombre' => 'Queso cheddar']], 1);
        $resLarge = $repo->calcularCostoExtras($producto, [['id_ingrediente' => 99, 'nombre' => 'Queso cheddar']], 2);

        $this->assertSame(400.0, (float) $resSmall['total']);
        $this->assertSame(900.0, (float) $resLarge['total']);
    }
}
