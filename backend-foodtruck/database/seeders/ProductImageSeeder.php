<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    protected function resolveProductImage(string $productName, array $productImages): ?string
    {
        $lookupKey = $this->normalizeProductName($productName);

        foreach ($productImages as $name => $image) {
            if ($this->normalizeProductName($name) === $lookupKey) {
                return $image;
            }
        }

        return null;
    }

    protected function resolveLocalImagePath(string $imageUrl, string $productName): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        if (preg_match('#^/?(?:storage/)?productos/.+#i', $imageUrl)) {
            return ltrim((string) preg_replace('#^/?(?:storage/)?#', '', $imageUrl), '/');
        }

        if (str_starts_with($imageUrl, 'productos/')) {
            return ltrim($imageUrl, '/');
        }

        if (str_starts_with($imageUrl, 'storage/')) {
            return preg_replace('#^/?storage/?#', '', $imageUrl);
        }

        if (str_starts_with($imageUrl, '/storage/')) {
            return preg_replace('#^/?storage/?#', '', ltrim($imageUrl, '/'));
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }

            $fileName = Str::slug($productName) . '-' . Str::random(6) . '.' . $extension;
            $destination = 'productos/' . $fileName;

            Storage::disk('public')->makeDirectory('productos');

            try {
                $response = Http::timeout(30)->get($imageUrl);
                if (!$response->successful()) {
                    return null;
                }

                Storage::disk('public')->put($destination, $response->body());
                return $destination;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    public function run(): void
    {
        $categoryImages = [
            'Vianesas' => 'https://images.unsplash.com/photo-1612392062798-7c7e16d7f49f?w=800&auto=format&fit=crop&q=80',
            'Ass' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&auto=format&fit=crop&q=80',
            'Churrascos' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&auto=format&fit=crop&q=80',
            'Lomitos' => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=800&auto=format&fit=crop&q=80',
            'Hamburguesas' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&auto=format&fit=crop&q=80',
            'Pizzas' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&auto=format&fit=crop&q=80',
            'Fajitas' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&auto=format&fit=crop&q=80',
            'Sándwich de Pollo' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=800&auto=format&fit=crop&q=80',
            'Papas & Chorrillanas' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800&auto=format&fit=crop&q=80',
            'Empanadas & Sopaipillas' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800&auto=format&fit=crop&q=80',
            'Bebidas frías' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=800&auto=format&fit=crop&q=80',
            'Bebidas calientes' => 'https://images.unsplash.com/photo-1497636577773-f1231844b336?w=800&auto=format&fit=crop&q=80',
            'Bebestibles & Jugos' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=800&auto=format&fit=crop&q=80',
            'Promos / Combos' => 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=800&auto=format&fit=crop&q=80',
        ];

        $productImages = [
            'Sopaipilla' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800&auto=format&fit=crop&q=80',
            'Empanada Individual' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800&auto=format&fit=crop&q=80',
            'Empanadas Queso 4x$1.000' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800&auto=format&fit=crop&q=80',
            'Empanadas Variadas 3x$1.000' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800&auto=format&fit=crop&q=80',
            'Chorrillana Tradicional' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800&auto=format&fit=crop&q=80',
            'Vianesa Completo' => 'productos/Completo_Completo.webp',
            'Vianesa Italiana' => 'productos/Completo_Italiano.webp',
            'Vianesa Dinámica' => 'productos/Completo_Dinamico.webp',
            'Ass Italiano' => 'productos/Ass_Italiano.webp',
            'Ass Dinámico' => 'productos/Ass_Dinamico.webp',
            'Ass Completo' => 'productos/Ass_Completo.webp',
            'Ass Barros Luco' => 'productos/Ass_Barros_Luco.webp',
            '2 Churrascos Promo' => 'productos/2_Churrascos_Promo.webp',
            '2 Hamburguesas Simples Promo' => 'productos/2_Hamburguesas_Simples_Promo.webp',
            '2 Hamburguesas Dobles Promo' => 'productos/2_Hamburguesas_Dobles_Promo.webp',
       

        ];

        foreach (Producto::with('categoria')->get() as $product) {
            $currentImage = trim((string) ($product->imagen ?? ''));
            $lookupKey = $this->normalizeProductName((string) $product->nombre);

            if (!empty($currentImage)) {
                if (str_starts_with($currentImage, 'productos/') || str_starts_with($currentImage, '/productos/') || str_starts_with($currentImage, 'storage/')) {
                    $normalizedCurrent = ltrim((string) preg_replace('#^/?(?:storage/)?#', '', $currentImage), '/');
                    if ($product->imagen !== $normalizedCurrent) {
                        $product->imagen = $normalizedCurrent;
                        $product->save();
                    }
                    continue;
                }

                if (str_starts_with($currentImage, 'http://') || str_starts_with($currentImage, 'https://')) {
                    $localPath = $this->resolveLocalImagePath($currentImage, $product->nombre);
                    if ($localPath && $product->imagen !== $localPath) {
                        $product->imagen = $localPath;
                        $product->save();
                    }
                    continue;
                }

                continue;
            }

            $imageUrl = $this->resolveProductImage((string) $product->nombre, $productImages);

            if (empty($imageUrl)) {
                $categoryName = $product->categoria ? $product->categoria->nombre_categoria : 'Varios';
                $imageUrl = $categoryImages[$categoryName] ?? $categoryImages['Promos / Combos'];
            }

            $localPath = $this->resolveLocalImagePath($imageUrl, $product->nombre);

            if ($localPath) {
                $product->imagen = $localPath;
                $product->save();
            }
        }
    }
}

