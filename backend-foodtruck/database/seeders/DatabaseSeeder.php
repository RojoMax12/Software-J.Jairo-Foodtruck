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
            ['nombre_categoria' => 'As', 'descripcion_categoria' => 'Sánguches estilo As en pan de completo'],
            ['nombre_categoria' => 'Churrascos', 'descripcion_categoria' => 'Sánguches con carne de churrasco de vacuno'],
            ['nombre_categoria' => 'Lomitos', 'descripcion_categoria' => 'Sánguches con sabroso lomito de cerdo'],
            ['nombre_categoria' => 'Hamburguesas', 'descripcion_categoria' => 'Hamburguesas caseras personalizables (incluyen 3 ingredientes)'],
            ['nombre_categoria' => 'Pizzas', 'descripcion_categoria' => 'Pizzas artesanales personalizables (incluyen 3 ingredientes)'],
            ['nombre_categoria' => 'Fajitas', 'descripcion_categoria' => 'Fajitas de pollo o carne personalizables (incluyen 3 ingredientes)'],
            ['nombre_categoria' => 'Sándwiches de Pollo', 'descripcion_categoria' => 'Sándwiches preparados con pechuga de pollo'],
            ['nombre_categoria' => 'Papas & Chorrillanas', 'descripcion_categoria' => 'Papas fritas, salchipapas, papas supremas y chorrillanas'],
            ['nombre_categoria' => 'Empanadas & Sopaipillas', 'descripcion_categoria' => 'Sopaipillas y empanadas variadas'],
            ['nombre_categoria' => 'Handrolls & Arrollados', 'descripcion_categoria' => 'Handrolls de pollo y carne'],
            ['nombre_categoria' => 'Bebidas frías', 'descripcion_categoria' => 'Gaseosas, jugos, aguas minerales y bebidas energéticas'],
            ['nombre_categoria' => 'Bebidas calientes', 'descripcion_categoria' => ' Cafés, té, hierbas e infusiones calientes'],
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
            'Mediano',
            'Grande',
            'Mediana',
            'Familiar',
            'XXL',
            'Solo Jugo de Huesillo',
            'Simple',
            'Doble',
            'Pollo',
            'Carne',
            'Mixta',
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
            ['nombre' => 'Ketchup', 'descripcion' => 'Aderezo ketchup', 'cantidad_actual' => 60, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Mostaza', 'descripcion' => 'Aderezo mostaza', 'cantidad_actual' => 60, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Mayonesa de ajo', 'descripcion' => 'Aderezo mayonesa de ajo', 'cantidad_actual' => 60, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa de queso', 'descripcion' => 'Aderezo salsa de queso', 'cantidad_actual' => 60, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa de ají', 'descripcion' => 'Aderezo salsa de ají', 'cantidad_actual' => 60, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa verde', 'descripcion' => 'Salsa verde tradicional', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Masa de pizzeta', 'descripcion' => 'Masa base para pizzeta', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa de tomate', 'descripcion' => 'Salsa de tomate natural', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Oregano', 'descripcion' => 'Orégano deshidratado', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Ciboulette', 'descripcion' => 'Ciboulette fresco picado', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Salsa cheddar', 'descripcion' => 'Salsa de queso cheddar', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Suprema de pollo', 'descripcion' => 'Suprema de pollo empanizada', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Handroll', 'descripcion' => 'Handroll de sushi', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Arrollado', 'descripcion' => 'Arrollado primavera', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Empanada Pollo Mandarín', 'descripcion' => 'Empanada de pollo mandarín', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
            ['nombre' => 'Empanada Carne Mandarin', 'descripcion' => 'Empanada de carne mandarín', 'cantidad_actual' => 40, 'cantidad_minima' => 5, 'fecha_de_ingreso' => '2026-01-01', 'disponible' => true],
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
                'precios_por_tamaño' => ['Chico' => 1600, 'Grande' => 2200, 'XXL' => 3000],
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
            
            // --- AS ---
            [
                'nombre' => 'As Italiano',
                'categoria' => 'As',
                'descripcion' => 'Carne trozada en pan de completo con tomate, palta y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 3000, 'Grande' => 3300],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Tomate', 'Palta', 'Mayonesa'
                ],
            ],
            [
                'nombre' => 'As Completo',
                'categoria' => 'As',
                'descripcion' => 'Carne trozada en pan de completo con tomate, salsa americana y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 3000, 'Grande' => 3300],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Tomate', 'Mayonesa', 'Salsa Americana', 'Chucrut'
                ],
            ],
            [
                'nombre' => 'As Dinámico',
                'categoria' => 'As',
                'descripcion' => 'Carne trozada en pan de completo con tomate, palta, chucrut, salsa americana y mayo (sin salsa verde)',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 3000, 'Grande' => 3300],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Tomate', 'Palta', 'Chucrut', 'Salsa Americana', 'Mayonesa'
                ],
            ],
            [
                'nombre' => 'As Barros Luco',
                'categoria' => 'As',
                'descripcion' => 'Carne trozada en pan de completo con abundante queso gauda derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 3000, 'Grande' => 3300],
                'ingredientes_defecto' => [
                    ['ingrediente' => 'Pan Chico', 'tamaño' => 'Chico', 'cantidad' => 1],
                    ['ingrediente' => 'Pan Grande', 'tamaño' => 'Grande', 'cantidad' => 1],
                    'Carne', 'Queso gauda', 'Mayonesa'
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
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Tomate', 'Porotos verdes', 'Mayonesa', 'Salsa de ají'],
            ],
            [
                'nombre' => 'Churrasco Barros Luco',
                'categoria' => 'Churrascos',
                'descripcion' => 'Carne de churrasco y abundante queso gauda derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3700],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Queso gauda', 'Mayonesa'],
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
                'precios_por_tamaño' => ['Único' => 4500],
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Cebolla', 'Huevo', 'Papas fritas', 'Mayonesa'],
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
                'ingredientes_defecto' => ['Pan Mediano', 'Lomito', 'Tomate', 'Porotos verdes', 'Mayonesa', 'Salsa de ají'],
            ],
            [
                'nombre' => 'Lomito Barros Luco',
                'categoria' => 'Lomitos',
                'descripcion' => 'Lomito de cerdo y queso gauda derretido',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3600],
                'ingredientes_defecto' => ['Pan Mediano', 'Lomito', 'Queso gauda', 'Mayonesa'],
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
                'nombre' => 'Pizza Familiar',
                'categoria' => 'Pizzas',
                'descripcion' => 'Pizza familiar recién horneada personalizable. Incluye 3 ingredientes a elección ($1.000 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 1000,
                'precios_por_tamaño' => ['Familiar' => 7500],
                'ingredientes_opcionales' => [
                    'Carne', 'Pollo', 'Lomito', 'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Pepinillo', 'Queso cheddar', 'Extra queso', 'Tomate',
                    'Papa hilo', 'Champiñón', 'Choclo', 'Aceitunas', 'Pimentón',
                    'Salame', 'Pepperoni', 'Jamón', 'Palmito'
                ],
            ],

            [
                'nombre' => 'Pizza Individual Napolitana',
                'categoria' => 'Pizzas',
                'descripcion' => 'Pizza individual recién horneada personalizable (Carne +$1.500, Tocino u otros extras +$1.000).',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 1000,
                'precios_por_tamaño' => ['Único' => 1500],
                'ingredientes_defecto' => ['Masa de pizzeta', 'Salsa de tomate', 'Queso gauda', 'Tomate', 'Oregano'],
                'ingredientes_opcionales' => [
                    ['ingrediente' => 'Carne', 'precio_extra' => 1500],
                    ['ingrediente' => 'Tocino', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pollo', 'precio_extra' => 1000],
                    ['ingrediente' => 'Jamón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pepperoni', 'precio_extra' => 1000],
                    ['ingrediente' => 'Salame', 'precio_extra' => 1000],
                    ['ingrediente' => 'Champiñón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Choclo', 'precio_extra' => 1000],
                    ['ingrediente' => 'Aceitunas', 'precio_extra' => 1000],
                    ['ingrediente' => 'Cebolla', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pimentón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Extra queso', 'precio_extra' => 1000],
                ],
            ],

            [
                'nombre' => 'Pizza Individual Pepperoni',
                'categoria' => 'Pizzas',
                'descripcion' => 'Pizza individual recién horneada personalizable (Carne +$1.500, Tocino u otros extras +$1.000).',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 1000,
                'precios_por_tamaño' => ['Único' => 1500],
                'ingredientes_defecto' => ['Masa de pizzeta', 'Salsa de tomate', 'Queso gauda', 'Pepperoni', 'Oregano'],
                'ingredientes_opcionales' => [
                    ['ingrediente' => 'Carne', 'precio_extra' => 1500],
                    ['ingrediente' => 'Tocino', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pollo', 'precio_extra' => 1000],
                    ['ingrediente' => 'Jamón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Salame', 'precio_extra' => 1000],
                    ['ingrediente' => 'Tomate', 'precio_extra' => 1000],
                    ['ingrediente' => 'Champiñón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Choclo', 'precio_extra' => 1000],
                    ['ingrediente' => 'Aceitunas', 'precio_extra' => 1000],
                    ['ingrediente' => 'Cebolla', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pimentón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Extra queso', 'precio_extra' => 1000],
                ],
            ],

            [
                'nombre' => 'Pizza Individual Salame',
                'categoria' => 'Pizzas',
                'descripcion' => 'Pizza individual recién horneada personalizable (Carne +$1.500, Tocino u otros extras +$1.000).',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 1000,
                'precios_por_tamaño' => ['Único' => 1500],
                'ingredientes_defecto' => ['Masa de pizzeta', 'Salsa de tomate', 'Queso gauda', 'Salame', 'Oregano'],
                'ingredientes_opcionales' => [
                    ['ingrediente' => 'Carne', 'precio_extra' => 1500],
                    ['ingrediente' => 'Tocino', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pollo', 'precio_extra' => 1000],
                    ['ingrediente' => 'Jamón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pepperoni', 'precio_extra' => 1000],
                    ['ingrediente' => 'Tomate', 'precio_extra' => 1000],
                    ['ingrediente' => 'Champiñón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Choclo', 'precio_extra' => 1000],
                    ['ingrediente' => 'Aceitunas', 'precio_extra' => 1000],
                    ['ingrediente' => 'Cebolla', 'precio_extra' => 1000],
                    ['ingrediente' => 'Pimentón', 'precio_extra' => 1000],
                    ['ingrediente' => 'Extra queso', 'precio_extra' => 1000],
                ],
            ],

            // --- FAJITAS (Personalizables, 3 ingredientes incluidos aparte de la proteína) ---
            [
                'nombre' => 'Fajita de pollo',
                'categoria' => 'Fajitas',
                'descripcion' => 'Fajita de pollo. Incluye 3 ingredientes a elección aparte de la proteína ($700 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 700,
                'precios_por_tamaño' => ['Pollo' => 3500],
                'ingredientes_opcionales' => [
                    'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Tomate', 'Lechuga', 'Palta', 'Pepinillo', 'Champiñón',
                    'Porotos verdes', 'Choclo', 'Aceitunas', 'Pimentón', 'Queso cheddar',
                    'Queso gauda', 'Salame', 'Filetillo de pollo', 'Papa hilo', 'Papas fritas'
                ],
            ],

            [
                'nombre' => 'Fajita de carne',
                'categoria' => 'Fajitas',
                'descripcion' => 'Fajita de carne. Incluye 3 ingredientes a elección aparte de la proteína ($700 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 700,
                'precios_por_tamaño' => ['Carne' => 3500],
                'ingredientes_opcionales' => [
                    'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Tomate', 'Lechuga', 'Palta', 'Pepinillo', 'Champiñón',
                    'Porotos verdes', 'Choclo', 'Aceitunas', 'Pimentón', 'Queso cheddar',
                    'Queso gauda', 'Salame', 'Filetillo de pollo', 'Papa hilo', 'Papas fritas'
                ],
            ],

            [
                'nombre' => 'Fajita de lomito',
                'categoria' => 'Fajitas',
                'descripcion' => 'Fajita de lomito. Incluye 3 ingredientes a elección aparte de la proteína ($700 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 700,
                'precios_por_tamaño' => ['Único' => 3500],
                'ingredientes_opcionales' => [
                    'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Tomate', 'Lechuga', 'Palta', 'Pepinillo', 'Champiñón',
                    'Porotos verdes', 'Choclo', 'Aceitunas', 'Pimentón', 'Queso cheddar',
                    'Queso gauda', 'Salame', 'Filetillo de pollo', 'Papa hilo', 'Papas fritas'
                ],
            ],

            [
                'nombre' => 'Fajita mixta',
                'categoria' => 'Fajitas',
                'descripcion' => 'Fajita mixta. Incluye 3 ingredientes a elección aparte de la proteína ($700 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 700,
                'precios_por_tamaño' => ['Mixta' => 4000],
                'ingredientes_opcionales' => [
                    'Tocino', 'Vianesa', 'Longaniza',
                    'Cebolla', 'Tomate', 'Lechuga', 'Palta', 'Pepinillo', 'Champiñón',
                    'Porotos verdes', 'Choclo', 'Aceitunas', 'Pimentón', 'Queso cheddar',
                    'Queso gauda', 'Salame', 'Filetillo de pollo', 'Papa hilo', 'Papas fritas'
                ],
            ],

            // --- SANDWICH DE POLLO ---
            [
                'nombre' => 'Sandwich de Pollo Italiano',
                'categoria' => 'Sándwiches de Pollo',
                'descripcion' => 'Exquisito sándwich de pechuga de pollo preparado',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3300],
                'ingredientes_defecto' => ['Pan Mediano', 'Pollo', 'Tomate', 'Lechuga', 'Mayonesa'],
            ],
            [
                'nombre' => 'Sandwich de Pollo Chacarero',
                'categoria' => 'Sándwiches de Pollo',
                'descripcion' => 'Exquisito sándwich de pechuga de pollo preparado estilo chacarero',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3700],
                'ingredientes_defecto' => ['Pan Mediano', 'Pollo', 'Tomate', 'Porotos verdes', 'Mayonesa', 'Salsa de ají'],
            ],
            [
                'nombre' => 'Suprema de pollo',
                'categoria' => 'Sándwiches de Pollo',
                'descripcion' => 'Pollo apanado. Incluye 3 ingredientes a elección ($500 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Único' => 3300],
                'ingredientes_defecto' => [
                    'Pan Frica',
                    ['ingrediente' => 'Suprema de pollo', 'tamaño' => 'Único', 'cantidad' => 1],
                ],
                'ingredientes_opcionales' => [
                    'Queso cheddar', 'Queso gauda', 'Tocino', 'Cebolla', 'Papa hilo',
                    'Papas fritas', 'Aros de cebolla', 'Tomate', 'Lechuga', 'Palta',
                    'Pepinillo', 'Champiñón', 'Huevo', 'Choclo', 'Aceitunas'
                ],
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
                'precios_por_tamaño' => ['Chico' => 3000, 'Mediana' => 4000, 'Grande' => 5000],
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
                'ingredientes_defecto' => ['Papas fritas', 'Salsa cheddar', 'Carne', 'Ciboulette'],
            ],
            [
                'nombre' => 'Papas y Filetillo',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas fritas con filetillo de pollo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 4500],
                'ingredientes_defecto' => ['Papas fritas', 'Filetillo de pollo'],
            ],
            [
                'nombre' => 'Chorrillana Tradicional',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas fritas, carne trozada, cebolla frita y huevo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 7500, 'Grande' => 12500],
                'ingredientes_defecto' => ['Papas fritas', 'Carne', 'Longaniza', 'Vianesa', 'Cebolla', 'Huevo'],
            ],

            // --- HANDROLL & ARROLLADOS ---

            [
                'nombre' => 'Handroll Pollo',
                'categoria' => 'Handrolls & Arrollados',
                'descripcion' => 'Rico handroll de pollo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 3000],
                'ingredientes_defecto' => ['Handroll'],
            ],
            [
                'nombre' => 'Arrollado de Jamón y Queso (Unidad)',
                'categoria' => 'Handrolls & Arrollados',
                'descripcion' => 'Delicioso arrollado de jamón y queso',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 600],
                'ingredientes_defecto' => ['Arrollado'],
            ],
            [
                'nombre' => 'Arrollado de Jamón y Queso (2x$1000)',
                'categoria' => 'Handrolls & Arrollados',
                'descripcion' => 'Promo de 2 arrollados de jamón y queso',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1000],
                'ingredientes_defecto' => ['Arrollado'],
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
                'nombre' => 'Empanada Pollo Mandarín',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Empanada frita de pollo mandarín',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 600],
                'ingredientes_defecto' => ['Empanada Pollo Mandarín'],
            ],
            [
                'nombre' => 'Empanada Carne Mandarín',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Empanada frita de carne mandarín',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 600],
                'ingredientes_defecto' => ['Empanada Carne Mandarin'],
            ],
            [
                'nombre' => 'Empanadas Queso 4x$1.000',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Promoción de 4 empanadas de queso',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1000],
                'ingredientes_defecto' => ['Queso gauda'],
            ],
            [
                'nombre' => 'Empanadas Variadas 3x$1.000',
                'categoria' => 'Empanadas & Sopaipillas',
                'descripcion' => 'Promoción de 3 empanadas variadas (Queso aceituna, Jamón queso, Queso champiñón, Napolitana)',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1000],
            ],

            // --- BEBIDAS CALIENTES ---
            [
                'nombre' => 'Té',
                'categoria' => 'Bebidas calientes',
                'descripcion' => 'Té caliente',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 500],
            ],
            [
                'nombre' => 'Café',
                'categoria' => 'Bebidas calientes',
                'descripcion' => 'Café caliente',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 600],
            ],
            [
                'nombre' => 'Café Espresso',
                'categoria' => 'Bebidas calientes',
                'descripcion' => 'Café expreso concentrado',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],

            // --- BEBIDAS FRÍAS ---
            [
                'nombre' => 'Agua Mineral',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Agua mineral 500ml',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Lata Coca-Cola',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Coca-Cola Zero',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Fanta',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Sprite',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Jugo en Lata',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Jugo en lata · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Kem',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Limón Soda',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Papaya',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Crush',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Lata Pepsi',
                'grupo' => 'Bebida en Lata',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa en lata 350ml · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
            ],
            [
                'nombre' => 'Bebida 1L',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Gaseosa 1 Litro',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1500],
            ],
            [
                'nombre' => 'Agua Más',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Agua saborizada Max 1.5L',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1500],
            ],
            [
                'nombre' => 'Jugo Benedictino',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Jugo embotellado Benedictino',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1700],
            ],
            [
                'nombre' => 'Jugo Del Valle',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Jugo Del Valle sabores',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 2000],
            ],

            // --- Reunión 11/10 y 11/26 ---
            [
                'nombre' => 'Mote con Huesillo',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Mote con huesillo artesanal: Chico ($1.000), Mediano ($2.000) o Solo Jugo de Huesillo ($1.500)',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 1000, 'Mediano' => 2000, 'Solo Jugo de Huesillo' => 1500],
            ],
            [
                'nombre' => 'Bebida Express Coca-Cola',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Fanta',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Sprite',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Jugo Express',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Jugo express · Línea Coca-Cola',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Kem',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Limón Soda',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Papaya',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Bilz',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Crush',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Bebida Express Pepsi',
                'grupo' => 'Bebida Express',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Bebida express · Línea CCU',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 800],
            ],
            [
                'nombre' => 'Papas Nuevas',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas nuevas con acompañamientos de la casa',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 6500, 'Grande' => 12500],
                'ingredientes_defecto' => ['Papas fritas'],
            ],
            [
                'nombre' => 'Papa Italiana',
                'categoria' => 'Papas & Chorrillanas',
                'descripcion' => 'Papas fritas con tomate, palta y mayo',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Chico' => 7000, 'Grande' => 12000],
                'ingredientes_defecto' => ['Papas fritas', 'Tomate', 'Palta', 'Mayonesa'],
            ],
            [
                'nombre' => 'Jugo Watts Botella',
                'categoria' => 'Bebidas frías',
                'descripcion' => 'Jugo Watt\'s en botella',
                'tipo_armado' => 'Estandar',
                'cantidad_incluida' => 0,
                'precio_ingrediente_extra' => 0,
                'precios_por_tamaño' => ['Único' => 1200],
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
                'ingredientes_defecto' => ['Pan Mediano', 'Carne', 'Tomate', 'Palta', 'Mayonesa'],
                'ingredientes_opcionales' => [
                    'Queso gauda', 'Queso cheddar', 'Tocino', 'Cebolla', 'Huevo', 'Papas fritas', 'Porotos verdes'
                ],
            ],
            [
                'nombre' => '2 Hamburguesas Simples Promo',
                'categoria' => 'Promos / Combos',
                'descripcion' => 'Promoción especial 2 hamburguesas simples. Incluye 3 ingredientes a elección ($500 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Simple' => 6000],
                'ingredientes_defecto' => [
                    'Pan Frica',
                    ['ingrediente' => 'Hamburguesa', 'tamaño' => 'Simple', 'cantidad' => 2],
                ],
                'ingredientes_opcionales' => [
                    'Queso cheddar', 'Queso gauda', 'Tocino', 'Cebolla', 'Papa hilo',
                    'Papas fritas', 'Aros de cebolla', 'Tomate', 'Lechuga', 'Palta',
                    'Pepinillo', 'Champiñón', 'Huevo', 'Choclo', 'Aceitunas'
                ],
            ],
            [
                'nombre' => '2 Hamburguesas Dobles Promo',
                'categoria' => 'Promos / Combos',
                'descripcion' => 'Promoción especial 2 hamburguesas dobles. Incluye 3 ingredientes a elección ($500 ingrediente extra)',
                'tipo_armado' => 'Personalizable',
                'cantidad_incluida' => 3,
                'precio_ingrediente_extra' => 500,
                'precios_por_tamaño' => ['Doble' => 7000],
                'ingredientes_defecto' => [
                    'Pan Frica',
                    ['ingrediente' => 'Hamburguesa', 'tamaño' => 'Doble', 'cantidad' => 4],
                ],
                'ingredientes_opcionales' => [
                    'Queso cheddar', 'Queso gauda', 'Tocino', 'Cebolla', 'Papa hilo',
                    'Papas fritas', 'Aros de cebolla', 'Tomate', 'Lechuga', 'Palta',
                    'Pepinillo', 'Champiñón', 'Huevo', 'Choclo', 'Aceitunas'
                ],
            ],
        ];

        // 9. Guardar Productos, Precios por Tamaño e Ingredientes Asociados
        foreach ($productosEstructurados as $prodData) {
            $cat = $categoriasMap->get($prodData['categoria']);
            if (!$cat) continue;

            $producto = Producto::updateOrCreate(
                ['nombre' => $prodData['nombre']],
                [
                    'id_categoria' => $cat->id_categoria,
                    'grupo' => $prodData['grupo'] ?? null,
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
                    Producto_Tamaño::updateOrCreate(
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

            // As Dinámico: sin salsa verde (reunión 11/10)
            if ($prodData['nombre'] === 'As Dinámico' && ($salsaVerde = $ingredientesMap->get('Salsa verde'))) {
                Producto_ingrediente::where('id_producto', $producto->id_producto)
                    ->where('id_ingrediente', $salsaVerde->id_ingrediente)
                    ->delete();
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
                    $precioExtra = is_array($ingInfo) ? ($ingInfo['precio_extra'] ?? null) : null;

                    $ingObj = $ingredientesMap->get($nombreIng);
                    $tamObj = $nombreTam ? $tamañosMap->get($nombreTam) : null;

                    if ($ingObj) {
                        Producto_ingrediente::updateOrCreate(
                            [
                                'id_producto' => $producto->id_producto,
                                'id_ingrediente' => $ingObj->id_ingrediente,
                                'id_tamaño' => $tamObj ? $tamObj->id_tamaño : null,
                            ],
                            [
                                'cantidad' => $cantidad,
                                'incluido_por_defecto' => false,
                                'precio_extra' => $precioExtra,
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
