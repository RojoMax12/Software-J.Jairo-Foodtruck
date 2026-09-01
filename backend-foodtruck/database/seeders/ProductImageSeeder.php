<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $catImages = [
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
            'Bebestibles & Jugos' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=800&auto=format&fit=crop&q=80',
            'Promos / Combos' => 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=800&auto=format&fit=crop&q=80',
        ];

        foreach (Producto::with('categoria')->get() as $p) {
            if (empty($p->imagen)) {
                $cName = $p->categoria ? $p->categoria->nombre_categoria : 'Varios';
                $p->imagen = $catImages[$cName] ?? 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&auto=format&fit=crop&q=80';
                $p->save();
            }
        }
    }
}

