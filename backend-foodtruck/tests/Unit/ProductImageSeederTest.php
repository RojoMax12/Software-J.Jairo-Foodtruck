<?php

namespace Tests\Unit;

use Database\Seeders\ProductImageSeeder;
use PHPUnit\Framework\TestCase;

class ProductImageSeederTest extends TestCase
{
    public function test_it_normalizes_product_names_before_lookup(): void
    {
        $seeder = new ProductImageSeeder();
        $reflection = new \ReflectionClass($seeder);
        $method = $reflection->getMethod('normalizeProductName');
        $method->setAccessible(true);

        $normalized = $method->invoke($seeder, 'Vianesa Dinámica');

        $this->assertSame('vianesa dinamica', $normalized);
    }

    public function test_it_finds_the_product_image_using_a_normalized_lookup(): void
    {
        $seeder = new ProductImageSeeder();
        $reflection = new \ReflectionClass($seeder);
        $method = $reflection->getMethod('resolveProductImage');
        $method->setAccessible(true);

        $productImages = [
            'vianesa completo' => 'productos/Completo_Completo.webp',
            'vianesa italiana' => 'productos/Completo_Italiano.webp',
            'vianesa dinamica' => 'productos/Completo_Dinamico.webp',
            'ass dinamico' => 'productos/Ass_Dinamico.webp',
        ];

        $image = $method->invoke($seeder, 'Vianesa Dinámica', $productImages);

        $this->assertSame('productos/Completo_Dinamico.webp', $image);
    }
}
