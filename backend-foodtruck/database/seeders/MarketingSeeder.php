<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MarketingController;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $storageDir = storage_path('app/public/carousel');
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Configuración inicial de fotografías del carrusel y avisos de cabecera
        $marketingConfig = [
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

        // Guardar de forma persistente en storage/app/marketing_config.json
        Storage::put('marketing_config.json', json_encode($marketingConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

