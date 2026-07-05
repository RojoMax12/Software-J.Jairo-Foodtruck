<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Detalle_Pedido;
use App\Models\Detalle_pedido_Ingrediente;
use App\Models\Estado_pago;
use App\Models\Estado_pedido;
use App\Models\Horario_atencion;
use App\Models\Ingrediente;
use App\Models\Movimientos;
use App\Models\Oferta;
use App\Models\Oferta_producto;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Producto_Tamaño;
use App\Models\Producto_ingrediente;
use App\Models\Rol;
use App\Models\Tamaño;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $roles = ['Admin', 'Cliente', 'Trabajador'];
        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre_rol' => $rol]);
        }

        $estadosPedido = ['Pendiente', 'En preparación', 'Listo', 'Entregado', 'Cancelado'];
        foreach ($estadosPedido as $estado) {
            Estado_pedido::firstOrCreate(['nombre' => $estado]);
        }

        $estadosPago = ['Pendiente', 'Pagado', 'Anulado'];
        foreach ($estadosPago as $estado) {
            Estado_pago::firstOrCreate(['nombre' => $estado]);
        }

        $categorias = [
            ['nombre_categoria' => 'Papas & Chorillanas', 'descripcion_categoria' => 'Deliciosos productos con papas y chorillanas para disfrutar en cualquier momento.'],
            ['nombre_categoria' => 'Vianesas', 'descripcion_categoria' => 'Deliciosos productos con vianesas para disfrutar en cualquier momento.'],
            ['nombre_categoria' => 'Sanguches / Bajones', 'descripcion_categoria' => 'Sanguches y bajones para disfrutar en cualquier momento.'],
            ['nombre_categoria' => 'Promos / Combos', 'descripcion_categoria' => 'Promociones y combos especiales'],
            ['nombre_categoria' => 'Masas', 'descripcion_categoria' => 'Productos con pan'],
            ['nombre_categoria' => 'Bebestibles', 'descripcion_categoria' => 'Bebidas'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['nombre_categoria' => $categoria['nombre_categoria']],
                ['descripcion_categoria' => $categoria['descripcion_categoria']]
            );
        }

        $tamaños = ['Pequeño', 'Mediana', 'Grande', 'Familiar', 'XXL'];
        foreach ($tamaños as $nombre) {
            Tamaño::firstOrCreate(['nombre' => $nombre]);
        }

        $adminRole = Rol::where('nombre_rol', 'Admin')->first();
        if ($adminRole) {
            Usuario::firstOrCreate(
                ['correo' => 'admin@foodtruck.test'],
                [
                    'id_rol' => $adminRole->id_rol,
                    'nombre' => 'Administrador',
                    'estado' => true,
                    'contrasena' => bcrypt('Admin1234'),
                ]
            );
        }

        Usuario::factory(10)->create();
        Caja::factory(5)->create();
        Producto::factory(15)->create();
        Producto_Tamaño::factory(20)->create();
        Ingrediente::factory(12)->create();
        Producto_ingrediente::factory(20)->create();
        Oferta::factory(6)->create();
        Oferta_producto::factory(10)->create();
        Pedido::factory(15)->create();
        Venta::factory(10)->create();
        Detalle_Pedido::factory(20)->create();
        Detalle_pedido_Ingrediente::factory(20)->create();
        Horario_atencion::factory(7)->create();
        Movimientos::factory(10)->create();

        $productosBase = [
            ['nombre' => 'Burger clásica', 'descripcion' => 'Carne, tomate, lechuga y queso', 'precio' => 8500, 'categoria' => 'Sanguches / Bajones'],
            ['nombre' => 'Burger BBQ', 'descripcion' => 'Carne, cebolla caramelizada y salsa BBQ', 'precio' => 9500, 'categoria' => 'Sanguches / Bajones'],
            ['nombre' => 'Papas fritas', 'descripcion' => 'Porción grande de papas', 'precio' => 3500, 'categoria' => 'Papas & Chorillanas'],
            ['nombre' => 'Chorillana', 'descripcion' => 'Papas fritas, carne, huevo y cebolla', 'precio' => 12000, 'categoria' => 'Papas & Chorillanas'],
            ['nombre' => 'Vianesa clásica', 'descripcion' => 'Vianesa con pan y salsa', 'precio' => 7000, 'categoria' => 'Vianesas'],
            ['nombre' => 'Vianesa BBQ', 'descripcion' => 'Vianesa con salsa BBQ y cebolla caramelizada', 'precio' => 8000, 'categoria' => 'Vianesas'],
            ['nombre' => 'Combo familiar', 'descripcion' => 'Incluye 2 burgers, papas fritas y 2 bebidas', 'precio' => 25000, 'categoria' => 'Promos / Combos'],
            ['nombre' => 'Combo pareja', 'descripcion' => 'Incluye 2 vianesas, papas fritas y 2 bebidas', 'precio' => 18000, 'categoria' => 'Promos / Combos'],
            ['nombre' => 'Pan de ajo', 'descripcion' => 'Delicioso pan de ajo recién horneado', 'precio' => 3000, 'categoria' => 'Masas'],
            ['nombre' => 'Pan de queso', 'descripcion' => 'Delicioso pan de queso recién horneado', 'precio' => 3500, 'categoria' => 'Masas'],
            ['nombre' => 'Agua mineral 500ml', 'descripcion' => 'Agua fría sin gas', 'precio' => 1500, 'categoria' => 'Bebestibles'],
            ['nombre' => 'Gaseosa 500ml', 'descripcion' => 'Bebida fría', 'precio' => 2000, 'categoria' => 'Bebestibles'],
        ];

        $categoriasDisponibles = Categoria::all();
        $tamañosDisponibles = Tamaño::all();

        foreach ($productosBase as $productoData) {
            $categoria = $categoriasDisponibles->firstWhere('nombre_categoria', $productoData['categoria']);
            if (! $categoria) {
                continue;
            }

            $producto = Producto::firstOrCreate(
                ['nombre' => $productoData['nombre']],
                [
                    'precio_ingrediente_extra' => 1000,
                    'tipo_armado' => 'predeterminado',
                    'cantidad_incluida' => 1,
                    'id_categoria' => $categoria->id_categoria,
                    'descripcion' => $productoData['descripcion'],
                ]
            );

            foreach ($tamañosDisponibles as $tamaño) {
                // Antes: ProductoTamaño (clase inexistente) y $tamaño->id (la PK real es id_tamaño).
                Producto_Tamaño::firstOrCreate(
                    [
                        'id_producto' => $producto->id_producto,
                        'id_tamaño' => $tamaño->id_tamaño,
                    ],
                    [
                        'precio' => $productoData['precio'] + ($tamaño->id_tamaño * 500),
                    ]
                );
            }
        }
    }
}
