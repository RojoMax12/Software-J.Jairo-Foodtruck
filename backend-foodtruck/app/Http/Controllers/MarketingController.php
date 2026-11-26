<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketingController extends Controller
{
    protected $storagePath = 'marketing_config.json';

    public static function getDefaultConfig()
    {
        return [
            'banners' => [
                [
                    'id' => 'banner-1',
                    'title' => 'Nuestras Mejores Vianesas y Completos XXL',
                    'subtitle' => 'El auténtico sabor tradicional de J.Jairo',
                    'image' => '/storage/carousel/banner1.webp',
                    'active' => true,
                    'order' => 1,
                ],
                [
                    'id' => 'banner-2',
                    'title' => 'Hamburguesas y Churrascos Premium',
                    'subtitle' => 'Carne 100% casera y pan fresco',
                    'image' => '/storage/carousel/banner2.webp',
                    'active' => true,
                    'order' => 2,
                ],
                [
                    'id' => 'banner-3',
                    'title' => 'Pizzas Artesanales y Sánguches',
                    'subtitle' => 'Pide online y retira al instante sin filas',
                    'image' => '/storage/carousel/banner3.webp',
                    'active' => true,
                    'order' => 3,
                ],
            ],
            'announcements' => [
                [
                    'id' => 'ann-1',
                    'badge' => 'PEDIDO ONLINE',
                    'type' => 'promo',
                    'text' => '¡Haz tu pedido en la web y retira directo en el foodtruck sin filas!',
                    'highlight' => '🌭 Retiro Rápido',
                    'active' => true,
                ],
                [
                    'id' => 'ann-2',
                    'badge' => 'HORARIOS',
                    'type' => 'schedule',
                    'text' => 'Dom a Jue: 19:00 a 00:30 hrs · Vie y Sáb: 19:00 a 01:30 hrs.',
                    'highlight' => '🔥 ¡Abierto hoy!',
                    'active' => true,
                ],
                [
                    'id' => 'ann-3',
                    'badge' => 'NUEVA CARTA',
                    'type' => 'new',
                    'text' => 'Prueba nuestras Hamburguesas Caseras XXL y Churrascos Premium.',
                    'highlight' => '🍔 100% Casero',
                    'active' => true,
                ],
                [
                    'id' => 'ann-4',
                    'badge' => 'ESTADO EN VIVO',
                    'type' => 'info',
                    'text' => 'Monitorea tu pedido en tiempo real desde la sección "Revisa tu pedido".',
                    'highlight' => '⚡ En vivo',
                    'active' => true,
                ],
                [
                    'id' => 'ann-5',
                    'badge' => 'MEDIOS DE PAGO',
                    'type' => 'payment',
                    'text' => 'Aceptamos Efectivo, Tarjeta de Débito, Crédito y Transferencia directa.',
                    'highlight' => '💳 Pago Fácil',
                    'active' => true,
                ],
            ],
            'autoPlayInterval' => 5000,
        ];
    }

    public function index()
    {
        if (Storage::exists($this->storagePath)) {
            $content = Storage::get($this->storagePath);
            $json = json_decode($content, true);
            if (is_array($json)) {
                return response()->json($json);
            }
        }

        $default = self::getDefaultConfig();
        Storage::put($this->storagePath, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json($default);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'banners' => 'sometimes|array|max:20',
            'banners.*.id' => 'required_with:banners|string|max:80',
            'banners.*.title' => 'nullable|string|max:180',
            'banners.*.subtitle' => 'nullable|string|max:250',
            'banners.*.image' => ['required_with:banners', 'string', 'max:7500000', 'regex:#^(?:/storage/|https?://|data:image/(?:jpeg|jpg|png|webp);base64,)#i'],
            'banners.*.active' => 'sometimes|boolean',
            'banners.*.order' => 'sometimes|integer|min:0|max:100',
            'announcements' => 'sometimes|array|max:30',
            'announcements.*.id' => 'required_with:announcements|string|max:80',
            'announcements.*.badge' => 'nullable|string|max:60',
            'announcements.*.type' => 'nullable|string|max:40',
            'announcements.*.text' => 'required_with:announcements|string|max:300',
            'announcements.*.highlight' => 'nullable|string|max:80',
            'announcements.*.active' => 'sometimes|boolean',
            'autoPlayInterval' => 'sometimes|integer|min:1000|max:60000',
        ]);

        $current = self::getDefaultConfig();
        if (Storage::exists($this->storagePath)) {
            $existing = json_decode(Storage::get($this->storagePath), true);
            if (is_array($existing)) {
                $current = $existing;
            }
        }

        if (array_key_exists('banners', $validated)) {
            $current['banners'] = array_map(fn ($banner) => $this->persistBannerImage($banner), $validated['banners']);
        }
        if (array_key_exists('announcements', $validated)) {
            $current['announcements'] = $validated['announcements'];
        }
        if (array_key_exists('autoPlayInterval', $validated)) {
            $current['autoPlayInterval'] = (int)$validated['autoPlayInterval'];
        }

        Storage::put($this->storagePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'success' => true,
            'message' => 'Configuración de marketing guardada en el servidor correctamente.',
            'data' => $current
        ]);
    }

    protected function persistBannerImage(array $banner): array
    {
        $raw = (string) ($banner['image'] ?? '');
        if (!str_starts_with($raw, 'data:image/')) {
            return $banner;
        }

        if (!preg_match('#^data:image/(jpeg|jpg|png|webp);base64,(.+)$#is', $raw, $matches)) {
            return $banner;
        }

        $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) > 5242880 || @getimagesizefromstring($binary) === false) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'banners' => ['La imagen del banner no es válida o supera los 5 MB.'],
            ]);
        }

        if (!Storage::disk('public')->exists('carousel')) {
            Storage::disk('public')->makeDirectory('carousel');
        }

        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($banner['id'] ?? 'banner'));
        $filename = 'carousel/' . ($safeId ?: 'banner') . '_' . time() . '_' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($filename, $binary);
        $banner['image'] = '/storage/' . $filename;

        return $banner;
    }
}