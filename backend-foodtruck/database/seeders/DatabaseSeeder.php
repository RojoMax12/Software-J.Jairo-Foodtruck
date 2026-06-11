<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['Admin', 'Dicreme', 'Distribuidor'];
        $estadosPedido = ['Validacion', 'Preparacion', 'Despachado', 'Entregado','Pendiente','Por pagar','Pagado', 'Cancelado'];
        $estadosCotizacion = ['Por Tomar','En Revision', 'Completado', 'Cancelado'];
        $categorias = ['Al agua', 'Leche de avena', 'Tradicional', 'Sin azúcar'];
        $formatos = ['10L', '5L', '2.5L', '1L'];
        
        foreach ($estadosCotizacion as $estado){
        \App\Models\Estado_cotizacion::firstOrCreate(['nombre_estado' => $estado]);
        }
        
        foreach ($roles as $rol) {
            \App\Models\Rol::firstOrCreate(['nombre_rol' => $rol]);
        }

        foreach ($estadosPedido as $estado) {
            \App\Models\Estado_pedido::firstOrCreate(['nombre_estado' => $estado]);
        }

        # Crear un usuario admin si no existe
        $adminRole = \App\Models\Rol::where('nombre_rol', 'Admin')->first();

        if ($adminRole) {
            \App\Models\Usuario_dicreme::firstOrCreate(
                ['correo_electronico' => 'admin@dicreme.cl'],
                [
                    'nombre_usuario' => 'admin',
                    'contrasena' => 'Admin1234',
                    'id_rol' => $adminRole->id,
                ]
            );
        }

        foreach ($categorias as $categoria) {
            \App\Models\Categoria::firstOrCreate(
                ['nombre_categoria' => $categoria],
                ['descripcion_categoria' => 'Categoría de helados '.$categoria]
            );
        }

        foreach ($formatos as $formato) {
            \App\Models\Formato::firstOrCreate(['nombre_formato' => $formato]);
        }

        \App\Models\Bodega::factory(5)->create();
        \App\Models\Stock::factory(10)->create();
        \App\Models\Producto::factory(132)->create();
        \App\Models\Lote::factory(25)->create();

        \App\Models\Usuario_dicreme::factory(10)->create();
        \App\Models\Usuario_distribuidores::factory(10)->create();
        \App\Models\Pedido::factory(30)->create();
        \App\Models\Venta::factory(20)->create();
        \App\Models\Despacho::factory(20)->create();
        \App\Models\Pedido_producto::factory(30)->create();
        \App\Models\Cotizacion::factory(30)->create();
        \App\Models\Cotizacion_producto::factory(30)->create();
    }
}



