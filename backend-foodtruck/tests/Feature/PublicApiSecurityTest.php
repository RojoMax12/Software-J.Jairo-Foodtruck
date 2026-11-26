<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\CheckRole;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicApiSecurityTest extends TestCase
{
    public function test_public_order_id_lookup_blocks_sequential_numeric_enumeration(): void
    {
        $response = $this->getJson('/api/public/pedidos/1');
        $response->assertStatus(404);

        $shortToken = $this->getJson('/api/public/pedidos/abc123');
        $shortToken->assertStatus(404);
    }

    public function test_protected_orders_and_admin_endpoints_require_authentication(): void
    {
        $this->getJson('/api/pedidos')->assertStatus(401);
        $this->postJson('/api/productos', ['nombre' => 'Test'])->assertStatus(401);
        $this->postJson('/api/marketing', ['banners' => []])->assertStatus(401);
    }

    public function test_marketing_store_persists_base64_banner_image_to_public_storage(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $this->withoutMiddleware([AuthenticateJwt::class, CheckRole::class]);

        // Imagen PNG mínima válida de 1x1 píxel en base64
        $tinyPngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

        $response = $this->postJson('/api/marketing', [
            'banners' => [
                [
                    'id' => 'banner-test-1',
                    'title' => 'Promo Test',
                    'subtitle' => 'Subtest',
                    'image' => $tinyPngBase64,
                    'active' => true,
                    'order' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $storedUrl = $response->json('data.banners.0.image');
        $this->assertStringStartsWith('/storage/carousel/banner-test-1_', (string) $storedUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', (string) $storedUrl));
    }

    public function test_role_middleware_denies_customer_access_to_admin_routes(): void
    {
        $this->withoutMiddleware([AuthenticateJwt::class]);

        $customer = new Usuario(['id_rol' => 2, 'nombre' => 'Cliente']);
        $this->actingAs($customer);

        $this->getJson('/api/usuarios')->assertStatus(403);
        $this->postJson('/api/horario_atenciones', [])->assertStatus(403);
    }
}

