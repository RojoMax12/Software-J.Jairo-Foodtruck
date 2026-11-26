<?php

namespace Tests\Unit;

use App\Http\Middleware\PurifyInput;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Producto_ingrediente;
use App\Models\Promocion;
use App\Repositories\PedidoRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityAndPricingLogicTest extends TestCase
{
    public function test_purify_input_strips_xss_vectors_while_preserving_valid_culinary_text_and_passwords(): void
    {
        $middleware = new PurifyInput();
        $request = Request::create('/api/public/pedidos', 'POST', [
            'nombre_persona' => 'Juan <script>alert(1)</script> Pérez & Co.',
            'notas' => '<img src=x onerror="fetch(\'https://evil.test\')">Sin mayo',
            'payload' => "javascript:alert('xss')",
            'password' => '<Secret#123>',
        ]);

        $middleware->handle($request, static fn () => new Response('ok'));

        $this->assertSame('Juan  Pérez & Co.', $request->input('nombre_persona'));
        $this->assertStringNotContainsStringIgnoringCase('onerror', (string) $request->input('notas'));
        $this->assertStringNotContainsStringIgnoringCase('javascript:', (string) $request->input('payload'));
        $this->assertSame('<Secret#123>', $request->input('password'));
    }

    public function test_calcular_costo_extras_charges_higher_priced_extras_deterministically(): void
    {
        $repo = new class extends PedidoRepository {
            public function calc($producto, array $agregados): array
            {
                return $this->calcularCostoExtras($producto, $agregados);
            }
        };

        $producto = new Producto([
            'nombre' => 'Hamburguesa Personalizable',
            'cantidad_incluida' => 1,
            'precio_ingrediente_extra' => 1000,
        ]);

        $pi1 = new Producto_ingrediente(['id_ingrediente' => 10, 'precio_extra' => 1500]);
        $pi1->setRelation('ingrediente', new Ingrediente(['id_ingrediente' => 10, 'nombre' => 'Carne', 'disponible' => true]));

        $pi2 = new Producto_ingrediente(['id_ingrediente' => 11, 'precio_extra' => 1000]);
        $pi2->setRelation('ingrediente', new Ingrediente(['id_ingrediente' => 11, 'nombre' => 'Tocino', 'disponible' => true]));

        $pi3 = new Producto_ingrediente(['id_ingrediente' => 12, 'precio_extra' => 0]);
        $pi3->setRelation('ingrediente', new Ingrediente(['id_ingrediente' => 12, 'nombre' => 'Ketchup', 'disponible' => true]));

        $producto->setRelation('ingredientes', collect([$pi1, $pi2, $pi3]));

        // Orden enviado al revés: el cupo incluido (1) debe aplicarse al menor costo ($1.000) y cobrar $1.500
        $res = $repo->calc($producto, [
            ['id_ingrediente' => 10, 'nombre' => 'Carne', 'precio' => 0],
            ['id_ingrediente' => 11, 'nombre' => 'Tocino', 'precio' => 0],
            ['id_ingrediente' => 12, 'nombre' => 'Ketchup', 'precio' => 999],
        ]);

        $this->assertSame(1500.0, $res['total']);
        $this->assertSame(1500.0, $res['precios_por_id'][10]);
        $this->assertSame(0.0, $res['precios_por_id'][11]);
        $this->assertSame(0, $res['precios_por_id'][12]);
    }

    public function test_calcular_costo_extras_rejects_unmapped_ingredient(): void
    {
        $repo = new class extends PedidoRepository {
            public function __construct()
            {
                $this->ingredientesCache = collect();
            }

            public function calc($producto, array $agregados): array
            {
                return $this->calcularCostoExtras($producto, $agregados);
            }
        };

        $producto = new Producto(['nombre' => 'Completo']);
        $producto->setRelation('ingredientes', collect());

        $this->expectException(\InvalidArgumentException::class);
        $repo->calc($producto, [['nombre' => 'Ingrediente Inventado']]);
    }

    public function test_resolver_precio_con_promocion_prioritizes_size_specific_promo(): void
    {
        $repo = new class extends PedidoRepository {
            public function getShiftWindow($referenceTime = null)
            {
                return ['shift_date' => now()->format('Y-m-d')];
            }

            public function resolvePromo($producto, ?int $sizeId, float $basePrice): float
            {
                return $this->resolverPrecioConPromocion($producto, $sizeId, $basePrice);
            }
        };

        $producto = new Producto(['nombre' => 'As Italiano']);
        $promoGeneral = new Promocion([
            'id_tamaño' => null,
            'precio_promocional' => 2800,
            'activo' => true,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addDay(),
        ]);
        $promoGrande = new Promocion([
            'id_tamaño' => 2,
            'precio_promocional' => 2500,
            'activo' => true,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addDay(),
        ]);
        $producto->setRelation('promocionesActivas', collect([$promoGeneral, $promoGrande]));

        $this->assertSame(2500.0, $repo->resolvePromo($producto, 2, 3300.0));
        $this->assertSame(2800.0, $repo->resolvePromo($producto, 1, 3000.0));
    }
}

