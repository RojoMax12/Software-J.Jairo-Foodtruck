<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    protected function normalizeProductName(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[\s_]+/', ' ', $normalized) ?? $normalized;

        $normalized = strtr($normalized, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);

        return trim($normalized);
    }

    protected function resolveProductImage(
        string $productName,
        array $productImages
    ): mixed {
        $lookupKey = $this->normalizeProductName($productName);

        foreach ($productImages as $name => $image) {
            if ($this->normalizeProductName($name) === $lookupKey) {
                return $image;
            }
        }

        return null;
    }

    public function run(): void
    {
        $productImages = [

            // VIANESAS
            'Vianesa Italiana' => 'productos/01-vianesa-italiana.jpg',
            'Vianesa Completo' => 'productos/02-vianesa-completo.jpg',
            'Vianesa Dinámica' => 'productos/03-vianesa-dinamica.jpg',

            // ASS
            'Ass Italiano' => 'productos/04-as-italiano.jpg',
            'Ass Completo' => 'productos/05-as-completo.jpg',
            'Ass Dinámico' => 'productos/06-as-dinamico.jpg',
            'Ass Barros Luco' => 'productos/07-as-luco.jpg',

            // CHURRASCOS
            'Churrasco Italiano' => 'productos/08-churrasco-italiano.jpg',
            'Churrasco Chacarero' => 'productos/09-churrasco-chacarero.jpg',
            'Churrasco Barros Luco' => 'productos/10-churrasco-barros-luco.jpg',
            'Churrasco Brasileño' => 'productos/11-churrasco-brasileno.jpg',
            'Churrasco a lo Pobre' => 'productos/12-churrasco-a-lo-pobre.jpg',

            // LOMITOS
            'Lomito Italiano' => 'productos/13-lomito-italiano.jpg',
            'Lomito Chacarero' => 'productos/14-lomito-chacarero.jpg',
            'Lomito Barros Luco' => 'productos/15-lomito-barros-luco.jpg',

            // HAMBURGUESAS
            'Hamburguesa Casera' => 'productos/16-hamburguesa-casera.jpg',

            // PIZZAS
            'Pizza Artesanal' => 'productos/17-pizza-artesanal.jpg',

            // FAJITAS
            'Fajita' => 'productos/18-fajita.jpg',

            // SÁNDWICH DE POLLO
            'Sandwich de Pollo' => 'productos/19-sandwich-de-pollo.jpg',

            // PAPAS & CHORRILLANAS
            'Papas Fritas' => 'productos/20-papas-fritas.jpg',
            'Salchipapas' => 'productos/21-salchipapas.jpg',
            'Papas Supremas' => 'productos/22-papas-supremas.jpg',
            'Chorrillana Tradicional' => 'productos/23-chorrillana.jpg',

            // EMPANADAS & SOPAIPILLAS
            'Sopaipilla' => 'productos/24-sopaipillas.jpg',
            'Empanada Individual' => 'productos/25-empanada-individual.jpg',
            'Empanadas Queso 4x$1.000' => 'productos/26-empanada-queso-4x1000.jpg',
            'Empanadas Variadas 3x$1.000' => 'productos/27-empanadas-variadas-3x1000.jpg',

            // BEBIDAS CALIENTES
            'Té' => 'productos/28-te.jpg',
            'Café' => 'productos/29-cafe.jpg',
            'Café Express' => 'productos/30-cafe-espresso.jpg',

            // BEBIDAS FRÍAS
            'Agua Mineral' => 'productos/31-agua-mineral.jpg',
            'Bebida en Lata' => 'productos/32-bebida-en-lata.jpg',
            'Bebida 1L' => 'productos/33-bebida-1l.jpg',

            // BEBESTIBLES & JUGOS
            'Agua Max' => 'productos/34-agua-mas.jpg',
            'Jugo Benedictino' => 'productos/35-jugo-benedictino.jpg',
            'Jugo Del Valle' => 'productos/36-jugo-del-valle.jpg',

            // PROMOCIONES
            '2 Churrascos Promo' => 'productos/37-2-churrascos-promo.jpg',
            '2 Hamburguesas Simples Promo' => 'productos/38-2-hamburguesas-simples-promo.jpg',
            '2 Hamburguesas Dobles Promo' => 'productos/39-2-hamburguesas-dobles-promo.jpg',
        ];

        // Punto focal opcional para imágenes recortadas con object-fit: cover.
        $imagePositions = [
            'Té' => '50% 60%',
            'Café' => '75% 30%',
            'Café Express' => '75% 50%',
            'Agua Max' => '50% 60%',
            'Jugo Benedictino' => '50% 60%',
            'Jugo Del Valle' => '50% 60%',
        ];

        // Escala opcional: 1.00 es el tamaño original, 1.15 equivale a 15% de zoom.
        $imageZooms = [
            'Té' => 1.00,
            'Café Express' => 1.00,
        ];

            $imageFits = [
                'Té' => 'cover',
                'Café' => 'cover',
                'Café Express' => 'cover',
                'Jugo del valle' => 'cover',
            ];

        foreach (Producto::all() as $product) {
            $imagePath = $this->resolveProductImage(
                (string) $product->nombre,
                $productImages
            );
            $imagePosition = $this->resolveProductImage(
                (string) $product->nombre,
                $imagePositions
            );
            $imageZoom = $this->resolveProductImage(
                (string) $product->nombre,
                $imageZooms
            );
                $imageFit = $this->resolveProductImage(
                    (string) $product->nombre,
                    $imageFits
                );

            if ($imagePath && $product->imagen !== $imagePath) {
                $product->imagen = $imagePath;
            }

            if ($imagePosition && $product->imagen_posicion !== $imagePosition) {
                $product->imagen_posicion = $imagePosition;
            }

            if ($imageZoom !== null && (float) $product->imagen_zoom !== (float) $imageZoom) {
                $product->imagen_zoom = $imageZoom;
            }

                if ($imageFit && $product->imagen_ajuste !== $imageFit) {
                    $product->imagen_ajuste = $imageFit;
                }

            if ($product->isDirty()) {
                $product->save();
            }
        }
    }
}

