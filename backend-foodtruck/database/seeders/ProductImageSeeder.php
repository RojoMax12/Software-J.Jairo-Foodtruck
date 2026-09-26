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

            // AS
            'As Italiano' => 'productos/04-as-italiano.jpg',
            'As Completo' => 'productos/05-as-completo.jpg',
            'As Dinámico' => 'productos/06-as-dinamico.jpg',
            'As Barros Luco' => 'productos/07-as-luco.jpg',

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
            'Pizza Familiar' => 'productos/17-pizza-familiar.jpg',
            'Pizza Individual Napolitana' => 'productos/18-pizza-individual-napolitana.jpg',
            'Pizza Individual Pepperoni' => 'productos/19-pizza-individual-pepperoni.jpg',
            'Pizza Individual Salame' => 'productos/20-pizza-individual-salame.jpg',

            // FAJITAS
            'Fajita de pollo' => 'productos/21-fajita-de-pollo.jpg',
            'Fajita de carne' => 'productos/22-fajita-de-carne.jpg',
            'Fajita de lomito' => 'productos/23-fajita-de-lomito.jpg',
            'Fajita mixta' => 'productos/24-fajita-mixta.jpg',

            // SÁNDWICH DE POLLO
            'Sandwich de Pollo Italiano' => 'productos/25-sandwich-de-pollo-italiano.jpg',
            'Sandwich de Pollo Chacarero' => 'productos/26-sandwich-de-pollo-chacarero.jpg',
            'Suprema de pollo' => 'productos/27-suprema-de-pollo.jpg',

            // PAPAS & CHORRILLANAS
            'Papas Fritas' => 'productos/28-papas-fritas.jpg',
            'Salchipapas' => 'productos/29-salchipapas.jpg',
            'Papas Supremas' => 'productos/30-papas-supremas.jpg',
            'Papas y Filetillo' => 'productos/31-papas-y-filetillos.jpg',
            'Chorrillana Tradicional' => 'productos/32-chorrillana.jpg',

            // HANDROLLS & ARROLLADOS
            'Handroll Pollo' => 'productos/33-handroll-pollo.jpg',
            'Arrollado de Jamón y Queso (Unidad)' => 'productos/34-arrollado-jamon-y-queso.jpg',
            'Arrollado de Jamón y Queso (2x$1000)' => 'productos/34-arrollado-jamon-y-queso.jpg',

            // EMPANADAS & SOPAIPILLAS
            'Sopaipilla' => 'productos/35-sopaipillas.jpg',
            'Empanada Pollo Mandarín' => 'productos/36-empanada-pollo-mandarin.jpg',
            'Empanada Carne Mandarín' => 'productos/37-empanada-carne-mandarin.jpg',
            'Empanadas Queso 4x$1.000' => 'productos/38-empanada-queso-4x1000.jpg',
            'Empanadas Variadas 3x$1.000' => 'productos/39-empanadas-variadas-3x1000.jpg',

            // BEBESTIBLES CALIENTES
            'Té' => 'productos/40-te.jpg',
            'Café' => 'productos/41-cafe.jpg',
            'Café Espresso' => 'productos/42-cafe-espresso.jpg',

            // BEBIDAS FRÍAS
            'Agua Mineral' => 'productos/43-agua-mineral.jpg',
            'Bebida en Lata' => 'productos/44-bebida-en-lata.jpg',
            'Bebida 1L' => 'productos/45-bebida-1l.jpg',
            'Agua Más' => 'productos/46-agua-mas.jpg',
            'Jugo Benedictino' => 'productos/47-jugo-benedictino.jpg',
            'Jugo Del Valle' => 'productos/48-jugo-del-valle.jpg',

            // PROMOS / COMBOS
            '2 Churrascos Promo' => 'productos/49-2-churrascos-promo.jpg',
            '2 Hamburguesas Simples Promo' => 'productos/50-2-hamburguesas-simples-promo.jpg',
            '2 Hamburguesas Dobles Promo' => 'productos/51-2-hamburguesas-dobles-promo.jpg',
        ];

        // Punto focal opcional para imágenes recortadas con object-fit: cover.
        $imagePositions = [
            'Té' => '50% 60%',
            'Café' => '75% 30%',
            'Café Espresso' => '75% 50%',
            'Agua Más' => '50% 60%',
            'Jugo Benedictino' => '50% 60%',
            'Jugo Del Valle' => '50% 60%',
        ];

        // Escala opcional: 1.00 es el tamaño original, 1.15 equivale a 15% de zoom.
        $imageZooms = [
            'Té' => 1.00,
            'Café Espresso' => 1.00,
        ];

            $imageFits = [
                'Té' => 'cover',
                'Café' => 'cover',
                'Café Espresso' => 'cover',
                'Jugo Del Valle' => 'cover',
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

