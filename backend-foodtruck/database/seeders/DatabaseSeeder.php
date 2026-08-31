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
        // 1. Roles
        $roles = ['Admin', 'Cliente', 'Trabajador'];
        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre_rol' => $rol]);
        }

        // Usuarios por defecto
        Usuario::firstOrCreate(
            ['correo' => 'admin@foodtruck.cl'],
            [
                'nombre' => 'Administrador',
                'telefono' => '+56900000000',
                'id_rol' => 1,
                'estado' => true,
                'contrasena' => \Illuminate\Support\Facades\Hash::make('admin123'),
            ]
        );

        Usuario::firstOrCreate(
            ['correo' => 'cocina@foodtruck.cl'],
            [
                'nombre' => 'Cocina',
                'telefono' => '+56911111111',
                'id_rol' => 3,
                'estado' => true,
                'contrasena' => \Illuminate\Support\Facades\Hash::make('cocina123'),
            ]
        );

        // 2. Estados de Pedido
        $estadosPedido = ['Pendiente', 'En preparación', 'Listo', 'Entregado', 'Cancelado'];
        foreach ($estadosPedido as $estado) {
            Estado_pedido::firstOrCreate(['nombre' => $estado]);
        }

        // 3. Estados de Pago
        $estadosPago = ['Pendiente', 'Pagado', 'Anulado'];
        foreach ($estadosPago as $estado) {
            Estado_pago::firstOrCreate(['nombre' => $estado]);
        }

        // 4. Categorías Reales
        $categorias = [
            ['nombre_categoria' => 'Vianesas', 'descripcion_categoria' => 'Completos y vianesas en pan tradicional o XXL'],
            ['nombre_categoria' => 'Ass', 'descripcion_categoria' => 'Sánguches estilo Ass en pan de completo'],
            ['nombre_categoria' => 'Churrascos', 'descripcion_categoria' => 'Sánguches con carne de churrasco de vacuno'],
            ['nombre_categoria' => 'Lomitos', 'descripcion_categoria' => 'Sánguches con sabroso lomito de cerdo'],
            ['nombre_categoria' => 'Hamburguesas', 'descripcion_categoria' => 'Hamburguesas caseras personalizables (incluyen 3 ingredientes)'],
            ['nombre_categoria' => 'Pizzas', 'descripcion_categoria' => 'Pizzas artesanales personalizables (incluyen 3 ingredientes)'],
            ['nombre_categoria' => 'Fajitas', 'descripcion_categoria' => 'Fajitas de pollo o carne personalizables (incluyen 3 ingredientes)'],
            ['nombre_categoria' => 'Sándwich de Pollo', 'descripcion_categoria' => 'Sándwiches preparados con pechuga de pollo'],
            ['nombre_categoria' => 'Papas & Chorrillanas', 'descripcion_categoria' => 'Papas fritas, salchipapas, papas supremas y chorrillanas'],
            ['nombre_categoria' => 'Empanadas & Sopaipillas', 'descripcion_categoria' => 'Sopaipillas y empanadas variadas'],
            ['nombre_categoria' => 'Bebestibles & Jugos', 'descripcion_categoria' => 'Bebidas frías, jugos, té y café'],
            ['nombre_categoria' => 'Promos / Combos', 'descripcion_categoria' => 'Promociones especiales y combos de la casa'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['nombre_categoria' => $categoria['nombre_categoria']],
                ['descripcion_categoria' => $categoria['descripcion_categoria']]
            );
        }

        // 5. Tamaños Reales
        $tamañosList = [
            'Único',
            'Chico',
            'Grande',
            'Mediana',
            'Familiar',
            'XXL',
            'Simple',
            'Doble',
            'Pollo',
            'Carne',
            '4x $1.000',
            '3x $1.000',
        ];
        foreach ($tamañosList as $nombreTamaño) {
            Tamaño::firstOrCreate(['nombre' => $nombreTamaño]);
        }

        // 6. Ingredientes Reales del Menú
        $ingredientes = [
            ['nombre' => 'Queso cheddar', 'descripcion' => 'Láminas de queso cheddar', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Queso gauda', 'descripcion' => 'Láminas de queso gauda', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Tocino', 'descripcion' => 'Tiras de tocino crujiente', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Cebolla', 'descripcion' => 'Cebolla picada o caramelizada', 'cantidad_actual' => 60, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Papa hilo', 'descripcion' => 'Papas hilo crujientes', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Papas fritas', 'descripcion' => 'Porción de papas fritas', 'cantidad_actual' => 100, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Aros de cebolla', 'descripcion' => 'Aros de cebolla empanizados', 'cantidad_actual' => 30, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Tomate', 'descripcion' => 'Tomate fresco en rodajas o cubos', 'cantidad_actual' => 80, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Lechuga', 'descripcion' => 'Lechuga fresca picada', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Palta', 'descripcion' => 'Palta molida', 'cantidad_actual' => 60, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pepinillo', 'descripcion' => 'Pepinillos en conserva', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Champiñón', 'descripcion' => 'Champiñones laminados', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Huevo', 'descripcion' => 'Huevo frito', 'cantidad_actual' => 60, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Choclo', 'descripcion' => 'Granos de choclo dulce', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Aceitunas', 'descripcion' => 'Aceitunas deshuesadas laminadas', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Carne', 'descripcion' => 'Churrasco de vacuno', 'cantidad_actual' => 80, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pollo', 'descripcion' => 'Pechuga de pollo trozada', 'cantidad_actual' => 70, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Lomito', 'descripcion' => 'Carne de cerdo lomito', 'cantidad_actual' => 60, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Vianesa', 'descripcion' => 'Vianesa tradicional', 'cantidad_actual' => 100, 'cantidad_minima' => 15, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Hamburguesa', 'descripcion' => 'Hamburger', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Longaniza', 'descripcion' => 'Longaniza en rodajas', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Extra queso', 'descripcion' => 'Porción extra de queso derretido', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pimentón', 'descripcion' => 'Pimentón rojo en tiras', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salame', 'descripcion' => 'Láminas de salame', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pepperoni', 'descripcion' => 'Láminas de pepperoni', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Jamón', 'descripcion' => 'Láminas de jamón de pierna', 'cantidad_actual' => 60, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Palmito', 'descripcion' => 'Palmitos en trozos', 'cantidad_actual' => 30, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Porotos verdes', 'descripcion' => 'Porotos verdes cocidos', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Filetillo de pollo', 'descripcion' => 'Tiras de filetillo de pollo', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Mayonesa', 'descripcion' => 'Mayonesa casera', 'cantidad_actual' => 100, 'cantidad_minima' => 10, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa BBQ', 'descripcion' => 'Salsa barbacoa', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa Americana', 'descripcion' => 'Salsa americana', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pan Frica', 'descripcion' => 'Pan frica', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pan Chico', 'descripcion' => 'Pan chico', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pan Mediano', 'descripcion' => 'Pan mediano', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pan Grande', 'descripcion' => 'Pan grande', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Pan XL', 'descripcion' => 'Pan XL', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Chucrut', 'descripcion' => 'Chucrut tradicional', 'cantidad_actual' => 50, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa verde', 'descripcion' => 'Salsa verde tradicional', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
        ];

        foreach ($ingredientes as $ingredienteData) {
            Ingrediente::firstOrCreate(
                ['nombre' => $ingredienteData['nombre']],
                [
                    'descripcion' => $ingredienteData['descripcion'],
                    'cantidad_actual' => $ingredienteData['cantidad_actual'],
                    'cantidad_minima' => $ingredienteData['cantidad_minima'],
                    'fecha_de_ingreso' => $ingredienteData['fecha_de_ingreso'],
                    'disponible' => $ingredienteData['disponible'],
                ]
            );
        }

        // 7. Usuarios Iniciales Reales (Admin y Trabajador)
        $adminRole = Rol::where('nombre_rol', 'Admin')->first();
        if ($adminRole) {
            Usuario::firstOrCreate(
                ['correo' => 'admin@foodtruck.test'],
                [
                    'id_rol' => $adminRole->id_rol,
                    'nombre' => 'Administrador Jairo',
                    'telefono' => '+56900000000',
                    'estado' => true,
                    'contrasena' => bcrypt('Admin1234'),
                ]
            );
        }

        $trabajadorRole = Rol::where('nombre_rol', 'Trabajador')->first();
        if ($trabajadorRole) {
            Usuario::firstOrCreate(
                ['correo' => 'caja@foodtruck.test'],
                [
                    'id_rol' => $trabajadorRole->id_rol,
                    'nombre' => 'Cajera Turno',
                    'telefono' => '+56900000000',
                    'estado' => true,
                    'contrasena' => bcrypt('Caja1234'),
                ]
            );
        }

        // Mapeo de auxilio
        $tamañosMap = Tamaño::all()->keyBy('nombre');
        $ingredientesMap = Ingrediente::all()->keyBy('nombre');
        $categoriasMap = Categoria::all()->keyBy('nombre_categoria');

        // 8. Lista Completa de Productos del Menú con sus Precios por Tamaño
        $productosEstructurados = [
            // --- VIANESAS ---
            [
                'nombre' => 'Vianesa Italiana',
                'categoria' => 'Vianesas',
                'descripcion' => 'Vianesa con tomate, palta y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 1400, 'Grande' => 1850, 'XXL' => 2600],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    ['ingrediente' => 'Pan XL', 'tamaño' => 'XXL', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'Grande', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'XXL', 'cantidad' => 2],
                    'Tomate', 'Palta', 'Mayonesa'
                ],
            ],
            [
                'nombre' => 'Vianesa Completo',
                'categoria' => 'Vianesas',
                'descripcion' => 'Vianesa con tomate, chucrut, salsa americana y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 1400, 'Grande' => 1850, 'XXL' => 2600],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    ['ingrediente' => 'Pan XL', 'tamaño' => 'XXL', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'Grande', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'XXL', 'cantidad' => 2],
                    'Tomate', 'Mayonesa', 'Salsa Americana', 'Chucrut'
                ],
            ],
            [
                'nombre' => 'Vianesa Dinámica',
                'categoria' => 'Vianesas',
                'descripcion' => 'Vianesa con palta, tomate, chucrut, salsa verde y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 1500, 'Grande' => 1900, 'XXL' => 2600],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    ['ingrediente' => 'Pan XL', 'tamaño' => 'XXL', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'Grande', 'cantidad' => 1],
                    ['ingrediente' => 'Vianesa', 'tamaño' => 'XXL', 'cantidad' => 2],
                    'Tomate', 'Palta', 'Mayonesa', 'Chucrut', 'Salsa Americana'
                ],
            ],
            
            // --- ASS ---
            [
                'nombre' => 'Ass Italiano',
                'categoria' => 'Ass',
                'descripcion' => 'Carne trozada en pan de completo con tomate, palta y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 2800, 'Grande' => 3200],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Tomate', 'Palta', 'Mayonesa'
                ],
            ],
            [
                'nombre' => 'Ass Completo',
                'categoria' => 'Ass',
                'descripcion' => 'Carne trozada en pan de completo con tomate, salsa americana y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 2700, 'Grande' => 3100],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Tomate', 'Mayonesa', 'Salsa Americana', 'Chucrut'
                ],
            ],
            [
                'nombre' => 'Ass Dinámico',
                'categoria' => 'Ass',
                'descripcion' => 'Carne trozada en pan de completo con combinación especial dinámica',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 2900, 'Grande' => 3300],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Tomate', 'Palta', 'Salsa verde', 'Mayonesa'
                ],
            ],
            [
                'nombre' => 'Ass Barros Luco',
                'categoria' => 'Ass',
                'descripcion' => 'Carne trozada en pan de completo con abundante queso gauda derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 2900, 'Grande' => 3300],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Queso gauda'
                ],
            ],


            // --- CHURRASCOS ---
            [
                'nombre' => 'Churrasco Italiano',
                'categoria' => 'Churrascos',
                'descripcion' => 'Carne de churrasco, tomate, palta y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3700],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Tomate', 'Palta', 'Mayonesa'],
            ],
            [
                'nombre' => 'Churrasco Chacarero',
                'categoria' => 'Churrascos',
                'descripcion' => 'Carne de churrasco, tomate y porotos verdes',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3700],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Tomate', 'Porotos verdes'],
            ],
            [
                'nombre' => 'Churrasco Barros Luco',
                'categoria' => 'Churrascos',
                'descripcion' => 'Carne de churrasco y abundante queso gauda derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3700],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Queso gauda'],
            ],
            [
                'nombre' => 'Churrasco Brasileño',
                'categoria' => 'Churrascos',
                'descripcion' => 'Carne de churrasco, palta y queso derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 4000],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Palta', 'Queso gauda'],
            ],
            [
                'nombre' => 'Churrasco a lo Pobre',
                'categoria' => 'Churrascos',
                'descripcion' => 'Carne de churrasco, cebolla frita, huevo frito y papas fritas',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 4000],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Cebolla', 'Huevo', 'Papas fritas'],
            ],

            // --- LOMITOS ---
            [
                'nombre' => 'Lomito Italiano',
                'categoria' => 'Lomitos',
                'descripcion' => 'Lomito de cerdo, tomate, palta y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3600],
                'ingredientes_defecto' => ['Pan Mediano', 'Lomito', 'Tomate', 'Palta', 'Mayonesa'],
            ],
            [
                'nombre' => 'Lomito Chacarero',
                'categoria' => 'Lomitos',
                'descripcion' => 'Lomito de cerdo, tomate y porotos verdes',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3600],
                'ingredientes_defecto' => ['Pan Mediano', 'Lomito', 'Tomate', 'Porotos verdes'],
            ],
            [
                'nombre' => 'Lomito Barros Luco',
                'categoria' => 'Lomitos',
                'descripcion' => 'Lomito de cerdo y queso gauda derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3600],
                'ingredientes_defecto' => ['Pan Mediano', 'Lomito', 'Queso gauda'],
            ],

            // --- HAMBURGUESAS (Personalizables, 3 ingredientes incluidos) ---
            [
                'nombre' => 'Hamburguesa Casera',
                'categoria' => 'Hamburguesas',
                'descripcion' => 'Hamburguesa casera personalizable. Incluye 3 ingredientes a elección ($500 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Simple' => 3300, 'Doble' => 4000],
                'ingredientes_defecto' => [
                    'Pan Frica',
                    ['ingrediente' => 'Hamburguesa', 'tamaño' => 'Simple', 'cantidad' => 1],
                    ['ingrediente' => 'Hamburguesa', 'tamaño' => 'Doble', 'cantidad' => 2],
                ],
                'ingredientes_opcionales' => [
                    'Queso cheddar', 'Queso gauda', 'Tocino', 'Cebolla', 'Papa hilo',
                    'Papas fritas', 'Aros de cebolla', 'Tomate', 'Lechuga', 'Palta',
                    'Pepinillo', 'Champiñón', 'Huevo', 'Choclo', 'Aceitunas'
                ],
            ],

            // --- PIZZAS (Personalizables, 3 ingredientes incluidos) ---
            [
                'nombre' => 'Pizza Artesanal',
                'categoria' => 'Pizzas',
                'descripcion' => 'Pizza recién horneada personalizable. Incluye 3 ingredientes a elección ($1.000 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 1000,
                'precios_por_tamaño' => ['Chico' => 1700, 'Familiar' => 7500],
                'ingredientes_opcionales' => [
                    'Carne', 'Pollo', 'Lomito', 'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Pepinillo', 'Queso cheddar', 'Extra queso', 'Tomate',
                    'Papa hilo', 'Champiñón', 'Choclo', 'Aceitunas', 'Pimentón',
                    'Salame', 'Pepperoni', 'Jamón', 'Palmito'
                ],
            ],

            // --- FAJITAS (Personalizables, 3 ingredientes incluidos aparte de la proteína) ---
            [
                'nombre' => 'Fajita',
                'categoria' => 'Fajitas',
                'descripcion' => 'Fajita de carne o pollo. Incluye 3 ingredientes a elección aparte de la proteína ($700 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 700,
                'precios_por_tamaño' => ['Pollo' => 3500, 'Carne' => 3500],
                'ingredientes_opcionales' => [
                    'Carne', 'Pollo', 'Lomito', 'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Tomate', 'Lechuga', 'Palta', 'Pepinillo', 'Champiñón',
                    'Porotos verdes', 'Choclo', 'Aceitunas', 'Pimentón', 'Queso cheddar',
                    'Queso gauda', 'Salame', 'Filetillo de pollo', 'Papa hilo', 'Papas fritas'
                ],
            ],

            // --- SANDWICH DE POLLO ---
            [
                'nombre' => 'Sandwich de Pollo',
                'categoria' => 'Sándwich de Pollo',
                'descripcion' => 'Exquisito sándwich de pollo preparado',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3300],
                'ingredientes_defecto' => ['Pan Mediano', 'Pollo', 'Tomate', 'Lechuga', 'Mayonesa'],
            ],

            // --- PAPAS & CHORRILLANAS ---
            [
                'nombre' => 'Papas Fritas',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Porción de papas fritas crujientes',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 1500, 'Mediana' => 2500, 'Grande' => 3500],
                'ingredientes_defecto' => ['Papas fritas'],
            ],
            [
                'nombre' => 'Salchipapas',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas fritas con trozos de vianesa',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 2000, 'Mediana' => 3000, 'Grande' => 4000],
                'ingredientes_defecto' => ['Papas fritas', 'Vianesa'],
            ],
            [
                'nombre' => 'Papas Supremas',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas fritas bañadas en queso, tocino y ciboulette',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 6500, 'Grande' => 11000],
                'ingredientes_defecto' => ['Papas fritas', 'Queso cheddar', 'Tocino'],
            ],
            [
                'nombre' => 'Chorrillana Tradicional',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas fritas, carne trozada, cebolla frita y huevo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 7500, 'Grande' => 12500],
                'ingredientes_defecto' => ['Papas fritas', 'Carne', 'Cebolla', 'Huevo'],
            ],

            // --- EMPANADAS & SOPAIPILLAS ---
            [
                'nombre' => 'Sopaipilla',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Sopaipilla crujiente',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 300],
            ],
            [
                'nombre' => 'Empanada Individual',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Empanada frita (Queso, Queso aceituna, Jamón queso, Queso champiñón, Napolitana)',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 550],
            ],
            [
                'nombre' => 'Empanadas Queso 4x$1.000',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Promoción de 4 empanadas de queso',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['4x $1.000' => 1000],
                'ingredientes_defecto' => ['Queso gauda'],
            ],
            [
                'nombre' => 'Empanadas Variadas 3x$1.000',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Promoción de 3 empanadas variadas (Queso aceituna, Jamón queso, Queso champiñón, Napolitana)',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['3x $1.000' => 1000],
            ],

            // --- BEBESTIBLES & JUGOS ---
            [
                'nombre' => 'Té',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Té caliente',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 500],
            ],
            [
                'nombre' => 'Café',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Café caliente',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 600],
            ],
            [
                'nombre' => 'Café Express',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Café expreso concentrado',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Agua Mineral',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Agua mineral 500ml',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida en Lata',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Gaseosa en lata 350ml',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Bebida 1L',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Gaseosa 1 Litro',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1500],
            ],
            [
                'nombre' => 'Agua Max',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Agua saborizada Max 1.5L',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1500],
            ],
            [
                'nombre' => 'Jugo Benedictino',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Jugo embotellado Benedictino',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1700],
            ],
            [
                'nombre' => 'Jugo Del Valle',
                'categoria' => 'Bebestibles & Jugos',
                'descripcion' => 'Jugo Del Valle sabores',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1900],
            ],

            // --- PROMOS / COMBOS ---
            [
                'nombre' => '2 Churrascos Promo',
                'categoria' => 'Promos / Combos',
                'descripcion' => 'Promoción especial de 2 churrascos a elección',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Único' => 7000],
                'ingredientes_defecto' => ['Carne'],
            ],
            [
                'nombre' => '2 Hamburguesas Simples Promo',
                'categoria' => 'Promos / Combos',
                'descripcion' => 'Promoción especial 2 hamburguesas simples',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Simple' => 6000],
            ],
            [
                'nombre' => '2 Hamburguesas Dobles Promo',
                'categoria' => 'Promos / Combos',
                'descripcion' => 'Promoción especial 2 hamburguesas dobles',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Doble' => 7000],
            ],
        ];

        // 9. Guardar Productos, Precios por Tamaño e Ingredientes Asociados
        foreach ($productosEstructurados as $prodData) {
            $cat = $categoriasMap->get($prodData['categoria']);
            if (!$cat) continue;

            $producto = Producto::firstOrCreate(
                ['nombre' => $prodData['nombre']],
                [
                    'id_categoria' => $cat->id_categoria,
                    'descripcion' => $prodData['descripcion'],
                    'tipo_armado' => $prodData['tipo_armado'],
                    'cantidad_incluida' => $prodData['cantidad_incluida'],
                    'precio_ingrediente_extra' => $prodData['precio_ingrediente_extra'],
                ]
            );

            // Precios por tamaño
            foreach ($prodData['precios_por_tamaño'] as $nombreTam => $precio) {
                $tamObj = $tamañosMap->get($nombreTam);
                if ($tamObj) {
                    Producto_Tamaño::firstOrCreate(
                        [
                            'id_producto' => $producto->id_producto,
                            'id_tamaño' => $tamObj->id_tamaño,
                        ],
                        [
                            'precio' => $precio,
                        ]
                    );
                }
            }

            // Ingredientes por defecto (si existen)
            if (!empty($prodData['ingredientes_defecto'])) {
                foreach ($prodData['ingredientes_defecto'] as $ingInfo) {
                    $nombreIng = is_array($ingInfo) ? $ingInfo['ingrediente'] : $ingInfo;
                    $nombreTam = is_array($ingInfo) ? ($ingInfo['tamaño'] ?? null) : null;
                    $cantidad = is_array($ingInfo) ? ($ingInfo['cantidad'] ?? 1) : 1;

                    $ingObj = $ingredientesMap->get($nombreIng);
                    $tamObj = $nombreTam ? $tamañosMap->get($nombreTam) : null;

                    if ($ingObj) {
                        Producto_ingrediente::firstOrCreate(
                            [
                                'id_producto' => $producto->id_producto,
                                'id_ingrediente' => $ingObj->id_ingrediente,
                                'id_tamaño' => $tamObj ? $tamObj->id_tamaño : null,
                            ],
                            [
                                'cantidad' => $cantidad,
                                'incluido_por_defecto' => true,
                            ]
                        );
                    }
                }
            }

            // Ingredientes opcionales para personalizar
            if (!empty($prodData['ingredientes_opcionales'])) {
                foreach ($prodData['ingredientes_opcionales'] as $ingInfo) {
                    $nombreIng = is_array($ingInfo) ? $ingInfo['ingrediente'] : $ingInfo;
                    $nombreTam = is_array($ingInfo) ? ($ingInfo['tamaño'] ?? null) : null;
                    $cantidad = is_array($ingInfo) ? ($ingInfo['cantidad'] ?? 1) : 1;

                    $ingObj = $ingredientesMap->get($nombreIng);
                    $tamObj = $nombreTam ? $tamañosMap->get($nombreTam) : null;

                    if ($ingObj) {
                        Producto_ingrediente::firstOrCreate(
                            [
                                'id_producto' => $producto->id_producto,
                                'id_ingrediente' => $ingObj->id_ingrediente,
                                'id_tamaño' => $tamObj ? $tamObj->id_tamaño : null,
                            ],
                            [
                                'cantidad' => $cantidad,
                                'incluido_por_defecto' => false,
                            ]
                        );
                    }
                }
            }
        }

        // 9. Seeder de Horarios de Atención Personalizables
        $horarios = [
            ['dia_semana' => 0, 'hora_apertura' => '19:00:00', 'hora_cierre' => '00:30:00', 'minuto_colchon' => 30], // Domingo
            ['dia_semana' => 1, 'hora_apertura' => '19:00:00', 'hora_cierre' => '00:30:00', 'minuto_colchon' => 30], // Lunes
            ['dia_semana' => 2, 'hora_apertura' => '19:00:00', 'hora_cierre' => '00:30:00', 'minuto_colchon' => 30], // Martes
            ['dia_semana' => 3, 'hora_apertura' => '19:00:00', 'hora_cierre' => '00:30:00', 'minuto_colchon' => 30], // Miércoles
            ['dia_semana' => 4, 'hora_apertura' => '19:00:00', 'hora_cierre' => '00:30:00', 'minuto_colchon' => 30], // Jueves
            ['dia_semana' => 5, 'hora_apertura' => '19:00:00', 'hora_cierre' => '01:30:00', 'minuto_colchon' => 30], // Viernes
            ['dia_semana' => 6, 'hora_apertura' => '19:00:00', 'hora_cierre' => '01:30:00', 'minuto_colchon' => 30], // Sábado
        ];

        foreach ($horarios as $h) {
            \App\Models\Horario_atencion::firstOrCreate(
                ['dia_semana' => $h['dia_semana']],
                [
                    'hora_apertura' => $h['hora_apertura'],
                    'hora_cierre' => $h['hora_cierre'],
                    'minuto_colchon' => $h['minuto_colchon'],
                    'activo' => true,
                ]
            );
        }

        $this->call(ProductImageSeeder::class);
        $this->call(MarketingSeeder::class);
    }
}
