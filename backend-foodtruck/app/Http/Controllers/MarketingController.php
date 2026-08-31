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
                    'text' => 'Atención de Lunes a Domingo de 19:00 a 00:30 hrs.',
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
        $current = self::getDefaultConfig();
        if (Storage::exists($this->storagePath)) {
            $existing = json_decode(Storage::get($this->storagePath), true);
            if (is_array($existing)) {
                $current = $existing;
            }
        }

        if ($request->has('banners')) {
            $current['banners'] = $request->input('banners');
        }
        if ($request->has('announcements')) {
            $current['announcements'] = $request->input('announcements');
        }
        if ($request->has('autoPlayInterval')) {
            $current['autoPlayInterval'] = (int)$request->input('autoPlayInterval');
        }

        Storage::put($this->storagePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'success' => true,
            'message' => 'Configuración de marketing guardada en el servidor correctamente.',
            'data' => $current
        ]);
    }
}

