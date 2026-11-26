<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Horario_atencion;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Producto_Tamaño;
use App\Models\Producto_ingrediente;
use App\Models\Tamaño;
use Illuminate\Database\Seeder;

/**
 * UpdateCatalogNov26Seeder
 *
 * Seeder incremental idempotente para bases de datos ya existentes.
 * Aplica todas las actualizaciones solicitadas sin duplicar registros.
 */
class UpdateCatalogNov26Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar tamaños y aderezos en inventario base
        foreach (['Único', 'Chico', 'Mediano', 'Mediana', 'Grande', 'XXL', 'Solo Jugo de Huesillo'] as $tamNombre) {
            Tamaño::firstOrCreate(['nombre' => $tamNombre]);
        }

        $aderezosNombres = ['Ketchup', 'Mostaza', 'Salsa BBQ', 'Mayonesa de ajo', 'Salsa de queso', 'Salsa de ají'];
        foreach ($aderezosNombres as $ad) {
            Ingrediente::firstOrCreate(
                ['nombre' => $ad],
                [
                    'descripcion' => "Aderezo {$ad}",
                    'cantidad_actual' => 60,
                    'cantidad_minima' => 5,
                    'fecha_de_ingreso' => now()->format('Y-m-d'),
                    'disponible' => true,
                ]
            );
        }

        $tamañosMap = Tamaño::all()->keyBy('nombre');
        $ingredientesMap = Ingrediente::all()->keyBy('nombre');

        // 2. Horarios de atención: Dom–Jue 19:00–00:30 (30m) / Vie–Sáb 19:00–01:30 (30m)
        foreach ([0, 1, 2, 3, 4] as $dia) {
            Horario_atencion::updateOrCreate(
                ['dia_semana' => $dia],
                ['hora_apertura' => '19:00:00', 'hora_cierre' => '00:30:00', 'minuto_colchon' => 30]
            );
        }
        foreach ([5, 6] as $dia) {
            Horario_atencion::updateOrCreate(
                ['dia_semana' => $dia],
                ['hora_apertura' => '19:00:00', 'hora_cierre' => '01:30:00', 'minuto_colchon' => 30]
            );
        }

        // 3. Salchipapas: +$500 en todas sus variantes (Chico $3.000, Mediana $4.000, Grande $5.000)
        $salchipapas = Producto::where('nombre', 'Salchipapas')->first();
        if ($salchipapas) {
            foreach (['Chico' => 3000, 'Mediana' => 4000, 'Grande' => 5000] as $tamNombre => $precio) {
                if ($tam = $tamañosMap->get($tamNombre)) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $salchipapas->id_producto, 'id_tamaño' => $tam->id_tamaño],
                        ['precio' => $precio]
                    );
                }
            }
        }

        // 4. Completo: Chico $1.600, Grande $2.200, XXL $3.000
        $completo = Producto::where('nombre', 'Vianesa Completo')->first();
        if ($completo) {
            foreach (['Chico' => 1600, 'Grande' => 2200, 'XXL' => 3000] as $tamNombre => $precio) {
                if ($tam = $tamañosMap->get($tamNombre)) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $completo->id_producto, 'id_tamaño' => $tam->id_tamaño],
                        ['precio' => $precio]
                    );
                }
            }
        }

        // 5. Ass (todos excepto "As Brasileño" / "Ass Brasileño"): Chico $3.000, Grande $3.300
        $catAs = Categoria::where('nombre_categoria', 'As')->first();
        $asProductos = Producto::where(function ($q) use ($catAs) {
            if ($catAs) {
                $q->where('id_categoria', $catAs->id_categoria)->orWhere('nombre', 'ilike', 'As %')->orWhere('nombre', 'ilike', 'Ass %');
            } else {
                $q->where('nombre', 'ilike', 'As %')->orWhere('nombre', 'ilike', 'Ass %');
            }
        })->get();

        foreach ($asProductos as $asProd) {
            $normAs = mb_strtolower($asProd->nombre, 'UTF-8');
            if (str_contains($normAs, 'brasil')) {
                continue; // Ass Brasileño mantiene su precio actual
            }
            foreach (['Chico' => 3000, 'Grande' => 3300] as $tamNombre => $precio) {
                if ($tam = $tamañosMap->get($tamNombre)) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $asProd->id_producto, 'id_tamaño' => $tam->id_tamaño],
                        ['precio' => $precio]
                    );
                }
            }
        }

        // 6. Churrasco a lo Pobre ($4.500), Papas Nuevas ($6.500/$12.500), Papa Italiana ($7.000/$12.000)
        if ($chPobre = Producto::where('nombre', 'Churrasco a lo Pobre')->first()) {
            if ($tamUnico = $tamañosMap->get('Único')) {
                Producto_Tamaño::updateOrCreate(
                    ['id_producto' => $chPobre->id_producto, 'id_tamaño' => $tamUnico->id_tamaño],
                    ['precio' => 4500]
                );
            }
        }

        $catPapas = Categoria::where('nombre_categoria', 'Papas & Chorrillanas')->first();
        if ($catPapas) {
            $papasNuevas = Producto::updateOrCreate(
                ['nombre' => 'Papas Nuevas'],
                [
                    'id_categoria' => $catPapas->id_categoria,
                    'descripcion' => 'Papas nuevas con acompañamientos de la casa',
                    'tipo_armado' => 'Estandar',
                    'cantidad_incluida' => 0,
                    'precio_ingrediente_extra' => 0,
                ]
            );
            foreach (['Chico' => 6500, 'Grande' => 12500] as $tamNombre => $precio) {
                if ($tam = $tamañosMap->get($tamNombre)) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $papasNuevas->id_producto, 'id_tamaño' => $tam->id_tamaño],
                        ['precio' => $precio]
                    );
                }
            }

            $papaItaliana = Producto::updateOrCreate(
                ['nombre' => 'Papa Italiana'],
                [
                    'id_categoria' => $catPapas->id_categoria,
                    'descripcion' => 'Papas fritas con tomate, palta y mayo',
                    'tipo_armado' => 'Estandar',
                    'cantidad_incluida' => 0,
                    'precio_ingrediente_extra' => 0,
                ]
            );
            foreach (['Chico' => 7000, 'Grande' => 12000] as $tamNombre => $precio) {
                if ($tam = $tamañosMap->get($tamNombre)) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $papaItaliana->id_producto, 'id_tamaño' => $tam->id_tamaño],
                        ['precio' => $precio]
                    );
                }
            }
        }

        // 7. Pizza Individual (Napolitana, Pepperoni, Salame): $1.500 + Carne (+$1.500), Tocino/otros (+$1.000)
        $pizzasIndividuales = [
            'Pizza Individual Napolitana' => 'Tomate',
            'Pizza Individual Pepperoni'  => 'Pepperoni',
            'Pizza Individual Salame'     => 'Salame',
        ];
        $pizzaExtrasBase = ['Carne', 'Tocino', 'Pollo', 'Jamón', 'Pepperoni', 'Salame', 'Tomate', 'Champiñón', 'Choclo', 'Aceitunas', 'Cebolla', 'Pimentón', 'Extra queso'];

        foreach ($pizzasIndividuales as $pizzaName => $baseTopping) {
            $pizza = Producto::where('nombre', $pizzaName)->first();
            if ($pizza) {
                $pizza->update([
                    'descripcion' => 'Pizza individual recién horneada personalizable (Carne +$1.500, Tocino u otros extras +$1.000).',
                    'tipo_armado' => 'Estandar',
                    'cantidad_incluida' => 0,
                    'precio_ingrediente_extra' => 1000,
                ]);
                if ($tamUnico = $tamañosMap->get('Único')) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $pizza->id_producto, 'id_tamaño' => $tamUnico->id_tamaño],
                        ['precio' => 1500]
                    );
                }

                foreach ($pizzaExtrasBase as $extraName) {
                    if ($extraName === $baseTopping) {
                        continue;
                    }
                    if ($ing = $ingredientesMap->get($extraName)) {
                        Producto_ingrediente::updateOrCreate(
                            [
                                'id_producto' => $pizza->id_producto,
                                'id_ingrediente' => $ing->id_ingrediente,
                                'id_tamaño' => null,
                            ],
                            [
                                'cantidad' => 1,
                                'incluido_por_defecto' => false,
                                'precio_extra' => $extraName === 'Carne' ? 1500 : 1000,
                            ]
                        );
                    }
                }
            }
        }

        // 8. Bebestibles y Mote con Huesillo
        $catBebidas = Categoria::where('nombre_categoria', 'Bebidas frías')->first();
        $tamUnico = $tamañosMap->get('Único');

        if ($catBebidas && $tamUnico) {
            // Jugo Del Valle ($2.000) & Jugo Watts Botella ($1.200)
            $jugoDelValle = Producto::updateOrCreate(
                ['nombre' => 'Jugo Del Valle'],
                ['id_categoria' => $catBebidas->id_categoria, 'descripcion' => 'Jugo Del Valle sabores', 'tipo_armado' => 'Estandar', 'cantidad_incluida' => 0, 'precio_ingrediente_extra' => 0]
            );
            Producto_Tamaño::updateOrCreate(['id_producto' => $jugoDelValle->id_producto, 'id_tamaño' => $tamUnico->id_tamaño], ['precio' => 2000]);

            $jugoWatts = Producto::updateOrCreate(
                ['nombre' => 'Jugo Watts Botella'],
                ['id_categoria' => $catBebidas->id_categoria, 'descripcion' => 'Jugo Watt\'s en botella', 'tipo_armado' => 'Estandar', 'cantidad_incluida' => 0, 'precio_ingrediente_extra' => 0]
            );
            Producto_Tamaño::updateOrCreate(['id_producto' => $jugoWatts->id_producto, 'id_tamaño' => $tamUnico->id_tamaño], ['precio' => 1200]);

            // Bebida Express ($800) agrupada en Línea Coca-Cola y Línea CCU
            $expressVariedades = [
                'Bebida Express Coca-Cola'   => 'Bebida express · Línea Coca-Cola',
                'Bebida Express Fanta'       => 'Bebida express · Línea Coca-Cola',
                'Bebida Express Sprite'      => 'Bebida express · Línea Coca-Cola',
                'Jugo Express'               => 'Jugo express · Línea Coca-Cola',
                'Bebida Express Kem'         => 'Bebida express · Línea CCU',
                'Bebida Express Limón Soda'  => 'Bebida express · Línea CCU',
                'Bebida Express Papaya'      => 'Bebida express · Línea CCU',
                'Bebida Express Bilz'        => 'Bebida express · Línea CCU',
                'Bebida Express Crush'       => 'Bebida express · Línea CCU',
                'Bebida Express Pepsi'       => 'Bebida express · Línea CCU',
            ];
            foreach ($expressVariedades as $nombre => $desc) {
                $p = Producto::updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'id_categoria' => $catBebidas->id_categoria,
                        'grupo' => 'Bebida Express',
                        'descripcion' => $desc,
                        'tipo_armado' => 'Estandar',
                        'cantidad_incluida' => 0,
                        'precio_ingrediente_extra' => 0,
                        'activo' => true,
                        'disponible' => true,
                    ]
                );
                Producto_Tamaño::updateOrCreate(['id_producto' => $p->id_producto, 'id_tamaño' => $tamUnico->id_tamaño], ['precio' => 800]);
            }

            // Bebida en Lata ($1.200) agrupada en Línea Coca-Cola y Línea CCU
            $latasVariedades = [
                'Lata Coca-Cola'      => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'Lata Coca-Cola Zero' => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'Lata Fanta'          => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'Lata Sprite'         => 'Gaseosa en lata 350ml · Línea Coca-Cola',
                'Jugo en Lata'        => 'Jugo en lata · Línea Coca-Cola',
                'Lata Kem'            => 'Gaseosa en lata 350ml · Línea CCU',
                'Lata Limón Soda'     => 'Gaseosa en lata 350ml · Línea CCU',
                'Lata Papaya'         => 'Gaseosa en lata 350ml · Línea CCU',
                'Lata Crush'          => 'Gaseosa en lata 350ml · Línea CCU',
                'Lata Pepsi'          => 'Gaseosa en lata 350ml · Línea CCU',
            ];
            foreach ($latasVariedades as $nombre => $desc) {
                $p = Producto::updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'id_categoria' => $catBebidas->id_categoria,
                        'grupo' => 'Bebida en Lata',
                        'descripcion' => $desc,
                        'tipo_armado' => 'Estandar',
                        'cantidad_incluida' => 0,
                        'precio_ingrediente_extra' => 0,
                        'activo' => true,
                        'disponible' => true,
                    ]
                );
                Producto_Tamaño::updateOrCreate(['id_producto' => $p->id_producto, 'id_tamaño' => $tamUnico->id_tamaño], ['precio' => 1200]);
            }

            // Desactivar antiguos registros genéricos de Express / Lata / Jugo de Huesillo suelto para evitar tarjetas duplicadas
            Producto::whereIn('nombre', ['Coca-Cola Express', 'Fanta y Sprite Express', 'Bebida Express', 'Bebida en Lata', 'Jugo de Huesillo'])
                ->update(['activo' => false]);

            // Mote con Huesillo: Chico $1.000, Mediano $2.000, Solo Jugo de Huesillo $1.500
            $mote = Producto::updateOrCreate(
                ['nombre' => 'Mote con Huesillo'],
                [
                    'id_categoria' => $catBebidas->id_categoria,
                    'descripcion' => 'Mote con huesillo artesanal: Chico ($1.000), Mediano ($2.000) o Solo Jugo de Huesillo ($1.500)',
                    'tipo_armado' => 'Estandar',
                    'cantidad_incluida' => 0,
                    'precio_ingrediente_extra' => 0,
                    'activo' => true,
                    'disponible' => true,
                ]
            );
            // Limpiar antiguo tamaño 'Mediana' si existía en Mote con Huesillo
            if ($tamMediana = $tamañosMap->get('Mediana')) {
                Producto_Tamaño::where('id_producto', $mote->id_producto)
                    ->where('id_tamaño', $tamMediana->id_tamaño)
                    ->delete();
            }
            foreach (['Chico' => 1000, 'Mediano' => 2000, 'Solo Jugo de Huesillo' => 1500] as $tamNombre => $precio) {
                if ($tam = $tamañosMap->get($tamNombre)) {
                    Producto_Tamaño::updateOrCreate(
                        ['id_producto' => $mote->id_producto, 'id_tamaño' => $tam->id_tamaño],
                        ['precio' => $precio]
                    );
                }
            }
        }

        // 9. Papa Suprema: quitar Tomate por defecto
        $papaSuprema = Producto::where('nombre', 'Papas Supremas')->first();
        $tomate = $ingredientesMap->get('Tomate');
        if ($papaSuprema && $tomate) {
            Producto_ingrediente::where('id_producto', $papaSuprema->id_producto)
                ->where('id_ingrediente', $tomate->id_ingrediente)
                ->where('incluido_por_defecto', true)
                ->delete();
        }

        // 10. Chacarero (Churrasco, Lomito, Pollo): asegurar Mayonesa y Salsa de ají por defecto para poder pedir Sin Mayo / Sin Ají
        $chacareros = Producto::whereIn('nombre', ['Churrasco Chacarero', 'Lomito Chacarero', 'Sandwich de Pollo Chacarero'])->get();
        $mayo = $ingredientesMap->get('Mayonesa');
        $aji = $ingredientesMap->get('Salsa de ají');
        foreach ($chacareros as $chac) {
            foreach ([$mayo, $aji] as $ing) {
                if ($ing) {
                    Producto_ingrediente::updateOrCreate(
                        ['id_producto' => $chac->id_producto, 'id_ingrediente' => $ing->id_ingrediente, 'id_tamaño' => null],
                        ['cantidad' => 1, 'incluido_por_defecto' => true]
                    );
                }
            }
        }

        // 11. As Dinámico: eliminar Salsa verde y garantizar Chucrut + Salsa Americana por defecto
        $asDinamico = Producto::where('nombre', 'As Dinámico')->first();
        if ($asDinamico) {
            if ($salsaVerde = $ingredientesMap->get('Salsa verde')) {
                Producto_ingrediente::where('id_producto', $asDinamico->id_producto)
                    ->where('id_ingrediente', $salsaVerde->id_ingrediente)
                    ->delete();
            }
            foreach (['Chucrut', 'Salsa Americana'] as $ingNombre) {
                if ($ing = $ingredientesMap->get($ingNombre)) {
                    Producto_ingrediente::updateOrCreate(
                        ['id_producto' => $asDinamico->id_producto, 'id_ingrediente' => $ing->id_ingrediente, 'id_tamaño' => null],
                        ['cantidad' => 1, 'incluido_por_defecto' => true]
                    );
                }
            }
            $asDinamico->update([
                'descripcion' => 'Carne trozada en pan de completo con tomate, palta, chucrut, salsa americana y mayo (sin salsa verde)',
            ]);
        }

        // 12. Limpiar aderezos antiguos de ingredientes opcionales en Hamburguesas/Fajitas (ahora tienen selector dedicado sin costo)
        $aderezoIds = Ingrediente::whereIn('nombre', $aderezosNombres)->pluck('id_ingrediente');
        if ($aderezoIds->isNotEmpty()) {
            $hamFajIds = Producto::whereHas('categoria', function ($q) {
                $q->whereIn('nombre_categoria', ['Hamburguesas', 'Fajitas']);
            })->orWhere('nombre', 'ilike', '%hamburguesa%')->pluck('id_producto');

            if ($hamFajIds->isNotEmpty()) {
                Producto_ingrediente::whereIn('id_producto', $hamFajIds)
                    ->whereIn('id_ingrediente', $aderezoIds)
                    ->where('incluido_por_defecto', false)
                    ->delete();
            }
        }

        // Reaplicar imágenes y textos de marketing actualizados
        $this->call(ProductImageSeeder::class);
        $this->call(MarketingSeeder::class);

        $this->command->info('✅ UpdateCatalogNov26Seeder completo aplicado con éxito.');
    }
}
