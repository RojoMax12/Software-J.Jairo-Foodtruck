<?php

namespace App\Repositories;

use App\Models\Caja;
use App\Models\Detalle_pedido;
use App\Models\Detalle_pedido_Ingrediente;
use App\Models\Pedido;
use App\Models\Horario_atencion;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Producto_Tamaño;
use App\Models\Producto_ingrediente;
use App\Models\Tamaño;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

# Repositorio Pedido
class PedidoRepository
{
    public function normalizeIngredienteNombre($valor)
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $texto = trim((string)$valor);
        if ($texto === '') {
            return null;
        }

        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = mb_strtolower($texto, 'UTF-8');
        return strtr($texto, [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
            'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
            'ñ'=>'n', 'Ñ'=>'n'
        ]);
    }

    public function resolverIdIngredienteDesdeNombre($nombre)
    {
        $nombreNormalizado = $this->normalizeIngredienteNombre($nombre);
        if ($nombreNormalizado === null) {
            return null;
        }

        $todosIngredientes = Ingrediente::all();

        // 1. Coincidencia exacta
        $ing = $todosIngredientes->first(function ($item) use ($nombreNormalizado) {
            return $this->normalizeIngredienteNombre($item->nombre) === $nombreNormalizado;
        });

        if ($ing) {
            return $ing->id_ingrediente;
        }

        // 2. Coincidencia parcial
        $ingParcial = $todosIngredientes->first(function ($item) use ($nombreNormalizado) {
            $norm = $this->normalizeIngredienteNombre($item->nombre);
            return $norm && (str_contains($norm, $nombreNormalizado) || str_contains($nombreNormalizado, $norm));
        });

        return $ingParcial ? $ingParcial->id_ingrediente : null;
    }

    public function getShiftWindow($referenceTime = null)
    {
        $now = $referenceTime ? \Carbon\Carbon::parse($referenceTime) : now();
        $dayOfWeek = (int)$now->format('w'); // 0 (Domingo) a 6 (Sábado)

        // 1. Horario de ayer (para madrugadas)
        $yesterdayDay = ($dayOfWeek + 6) % 7;
        $horarioAyer = Horario_atencion::where('dia_semana', $yesterdayDay)
            ->where('activo', true)
            ->first();

        $horaAyerAp = $horarioAyer ? $horarioAyer->hora_apertura : '19:00:00';
        $horaAyerCi = $horarioAyer ? $horarioAyer->hora_cierre : '00:30:00';
        $colchonAyer = $horarioAyer ? (int)($horarioAyer->minuto_colchon ?? 30) : 30;

        $partsAyerAp = explode(':', $horaAyerAp);
        $partsAyerCi = explode(':', $horaAyerCi);

        $yesterdayShiftStart = $now->copy()->subDay()->setTime((int)$partsAyerAp[0], (int)($partsAyerAp[1] ?? 0), 0);
        $yesterdayShiftEnd = $now->copy()->subDay()->setTime((int)$partsAyerCi[0], (int)($partsAyerCi[1] ?? 0), 0);

        if ((int)$partsAyerCi[0] < (int)$partsAyerAp[0]) {
            $yesterdayShiftEnd->addDay();
        }
        $yesterdayShiftEndWithBuffer = $yesterdayShiftEnd->copy()->addMinutes($colchonAyer);

        if ($now->lessThanOrEqualTo($yesterdayShiftEndWithBuffer) && $now->greaterThanOrEqualTo($yesterdayShiftStart)) {
            return [
                'start' => $yesterdayShiftStart,
                'end' => $yesterdayShiftEndWithBuffer,
                'hora_apertura' => $horaAyerAp,
                'hora_cierre' => $horaAyerCi,
                'dia' => 'Madrugada (Turno de ayer)',
                'is_active' => true,
                'shift_date' => $yesterdayShiftStart->format('Y-m-d')
            ];
        }

        // 2. Horario de hoy
        $horarioHoy = Horario_atencion::where('dia_semana', $dayOfWeek)
            ->where('activo', true)
            ->first();

        $horaHoyAp = $horarioHoy ? $horarioHoy->hora_apertura : '19:00:00';
        $horaHoyCi = $horarioHoy ? $horarioHoy->hora_cierre : '00:30:00';
        $colchonHoy = $horarioHoy ? (int)($horarioHoy->minuto_colchon ?? 30) : 30;

        $partsHoyAp = explode(':', $horaHoyAp);
        $partsHoyCi = explode(':', $horaHoyCi);

        $todayShiftStart = $now->copy()->setTime((int)$partsHoyAp[0], (int)($partsHoyAp[1] ?? 0), 0);
        $todayShiftEnd = $now->copy()->setTime((int)$partsHoyCi[0], (int)($partsHoyCi[1] ?? 0), 0);

        if ((int)$partsHoyCi[0] < (int)$partsHoyAp[0]) {
            $todayShiftEnd->addDay();
        }
        $todayShiftEndWithBuffer = $todayShiftEnd->copy()->addMinutes($colchonHoy);

        if ($now->greaterThanOrEqualTo($todayShiftStart) && $now->lessThanOrEqualTo($todayShiftEndWithBuffer)) {
            return [
                'start' => $todayShiftStart,
                'end' => $todayShiftEndWithBuffer,
                'hora_apertura' => $horaHoyAp,
                'hora_cierre' => $horaHoyCi,
                'dia' => 'Hoy',
                'is_active' => true,
                'shift_date' => $todayShiftStart->format('Y-m-d')
            ];
        }

        // 3. Durante el día antes de abrir: el turno es hoy cuando abra a su hora oficial
        return [
            'start' => $todayShiftStart,
            'end' => $todayShiftEndWithBuffer,
            'hora_apertura' => $horaHoyAp,
            'hora_cierre' => $horaHoyCi,
            'dia' => 'Hoy (Abre a las ' . substr($horaHoyAp, 0, 5) . ')',
            'is_active' => false,
            'shift_date' => $todayShiftStart->format('Y-m-d')
        ];
    }

    public function getShiftStart()
    {
        $window = $this->getShiftWindow();
        return $window['start'];
    }

    public function getPedidoByComandaTurno($numeroComanda, $targetDate = null)
    {
        $window = $this->getShiftWindow($targetDate);

        $relations = [
            'detalles.producto',
            'detalles.tamano',
            'detalles.tamaño',
            'detalles.ingredientes.ingrediente',
            'estadoPedido',
            'estadoPago',
            'usuario',
        ];

        $pedido = Pedido::with($relations)
            ->where('numero_pedido_dia', (int)$numeroComanda)
            ->whereBetween('created_at', [$window['start'], $window['end']])
            ->orderBy('id_pedido', 'desc')
            ->first();

        return [
            'pedido' => $pedido,
            'jornada' => [
                'inicio' => $window['start']->format('Y-m-d H:i:s'),
                'fin' => $window['end']->format('Y-m-d H:i:s'),
                'hora_apertura' => substr($window['hora_apertura'] ?? '19:00', 0, 5),
                'hora_cierre' => substr($window['hora_cierre'] ?? '00:30', 0, 5),
                'es_jornada_activa' => now()->between($window['start'], $window['end'])
            ]
        ];
    }

    # Create
    public function createPedido($data)
    {
        return DB::transaction(function () use ($data) {
            $shiftStart = $this->getShiftStart();
            $maxNumeroShift = Pedido::where('created_at', '>=', $shiftStart)->max('numero_pedido_dia') ?? 0;
            $numeroPedidoDia = $maxNumeroShift + 1;

            $items = $data['items'] ?? ($data['detalles'] ?? []);
            $totalCalculado = 0;

            $pedidoData = [
                'id_estado_pedido' => 1, // Todo nuevo pedido nace estrictamente en estado Pendiente (1)
                'id_estado_pago' => (int)($data['id_estado_pago'] ?? 1),
                'id_usuario' => $data['id_usuario'] ?? null,
                'numero_pedido_dia' => $numeroPedidoDia,
                'nombre_persona' => $data['nombre_persona'] ?? ($data['persona_recibe'] ?? 'Cliente'),
                'numero_telefono' => $data['numero_telefono'] ?? ($data['telefono'] ?? ''),
                'metodo_pago' => $data['metodo_pago'] ?? 'Efectivo',
                'fecha' => now(),
                'total' => 0, // Se actualizará con el recálculo verificado
                'notas' => $data['notas'] ?? null,
                'inventario_descontado' => false,
            ];

            $pedido = Pedido::create($pedidoData);

            foreach ($items as $item) {
                $rawProd = $item['id_producto'] ?? ($item['id'] ?? null);
                if (is_string($rawProd) && strpos($rawProd, '_') !== false) {
                    $rawProd = explode('_', $rawProd)[0];
                }
                $idProducto = is_numeric($rawProd) ? (int)$rawProd : null;

                $rawTamano = $item['id_tamaño'] ?? ($item['id_tamano'] ?? ($item['id_size'] ?? 1));
                if (is_string($rawTamano) && strpos($rawTamano, '_') !== false) {
                    $rawTamano = explode('_', $rawTamano)[0];
                }
                $idTamaño = is_numeric($rawTamano) ? (int)$rawTamano : 1;

                $cantidad = max(1, is_numeric($item['cantidad'] ?? ($item['quantity'] ?? 1)) 
                    ? (int)($item['cantidad'] ?? ($item['quantity'] ?? 1)) 
                    : 1);

                if ($idProducto) {
                    $productoModel = Producto::find($idProducto);

                    // 1. Recalcular precio base del producto desde la BD
                    $precioBase = 0;
                    if ($productoModel) {
                        $tamanoPrecio = Producto_Tamaño::where('id_producto', $idProducto)
                            ->where('id_tamaño', $idTamaño)
                            ->value('precio');

                        if ($tamanoPrecio !== null && (float)$tamanoPrecio > 0) {
                            $precioBase = (float)$tamanoPrecio;
                        } else {
                            $primerPrecio = Producto_Tamaño::where('id_producto', $idProducto)->value('precio');
                            $precioBase = $primerPrecio ? (float)$primerPrecio : (float)($productoModel->precio ?? $productoModel->precio_base ?? 0);
                        }
                    }

                    // Fallback seguro si el catálogo no tiene precio configurado
                    if ($precioBase <= 0 && isset($item['precio_unitario']) && is_numeric($item['precio_unitario'])) {
                        $precioBase = (float)$item['precio_unitario'];
                    }

                    // 2. Calcular costo de extras / agregados cobrables
                    $rawAgregados = $item['agregados'] ?? ($item['addedExtras'] ?? ($item['agregadosDetails'] ?? []));
                    $numExtras = is_array($rawAgregados) ? count($rawAgregados) : 0;
                    $incluidos = (int)($productoModel->cantidad_incluida ?? 0);
                    $precioExtra = (float)($productoModel->precio_ingrediente_extra ?? 0);
                    $extrasCobrables = max(0, $numExtras - $incluidos);
                    $costoExtras = $extrasCobrables * $precioExtra;

                    $precioUnitarioFinal = $precioBase + $costoExtras;
                    $subtotalItem = $precioUnitarioFinal * $cantidad;
                    $totalCalculado += $subtotalItem;

                    $detalle = Detalle_pedido::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_producto' => $idProducto,
                        'id_tamaño' => $idTamaño,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitarioFinal,
                    ]);

                    $allMods = [];

                    $rawMods = $item['opciones_seleccionadas'] ?? ($item['modificaciones'] ?? ($item['ingredientes'] ?? []));
                    if (is_array($rawMods)) {
                        foreach ($rawMods as $m) {
                            $allMods[] = $m;
                        }
                    }

                    $rawExcluidos = $item['excluidos'] ?? ($item['ingredientes_excluidos'] ?? ($item['removedIngredients'] ?? []));
                    if (is_array($rawExcluidos)) {
                        foreach ($rawExcluidos as $ex) {
                            $allMods[] = [
                                'tipo' => 'Exclusión',
                                'ingrediente' => is_array($ex) ? ($ex['nombre'] ?? $ex['id_ingrediente'] ?? $ex['id'] ?? '') : $ex,
                                'id_ingrediente' => is_array($ex) ? ($ex['id_ingrediente'] ?? $ex['id'] ?? null) : (is_numeric($ex) ? (int)$ex : null),
                                'precio' => 0,
                            ];
                        }
                    }

                    if (is_array($rawAgregados)) {
                        foreach ($rawAgregados as $ag) {
                            $allMods[] = [
                                'tipo' => 'Agregado',
                                'ingrediente' => is_array($ag) ? ($ag['nombre'] ?? $ag['name'] ?? $ag['id_ingrediente'] ?? '') : $ag,
                                'id_ingrediente' => is_array($ag) ? ($ag['id_ingrediente'] ?? $ag['id'] ?? null) : (is_numeric($ag) ? (int)$ag : null),
                                'precio' => is_array($ag) ? ($ag['precio'] ?? $ag['price'] ?? 0) : 0,
                            ];
                        }
                    }

                    $processedMods = [];
                    foreach ($allMods as $mod) {
                        $rawTipo = is_array($mod) ? ($mod['tipo'] ?? $mod['tipo_modificacion'] ?? 'Exclusión') : 'Exclusión';
                        $lowerTipo = strtolower((string)$rawTipo);
                        if (strpos($lowerTipo, 'tamaño') !== false || strpos($lowerTipo, 'tamano') !== false || strpos($lowerTipo, 'size') !== false) {
                            continue;
                        }

                        $tipoMod = (strpos($lowerTipo, 'exclu') !== false || strpos($lowerTipo, 'sin') !== false || strpos($lowerTipo, 'quit') !== false)
                            ? 'Exclusión'
                            : 'Agregado';

                        $precioAplicado = is_array($mod) ? ($mod['precio'] ?? $mod['precio_aplicado'] ?? 0) : 0;
                        $idIngrediente = is_array($mod) ? ($mod['id_ingrediente'] ?? $mod['id'] ?? null) : (is_numeric($mod) ? (int)$mod : null);

                        if (!$idIngrediente) {
                            $rawName = is_array($mod) ? ($mod['ingrediente'] ?? $mod['nombre'] ?? $mod['name'] ?? null) : $mod;
                            if ($rawName && is_string($rawName)) {
                                $idIngrediente = $this->resolverIdIngredienteDesdeNombre($rawName);
                            }
                        }

                        if ($idIngrediente) {
                            $modKey = $idIngrediente . '_' . strtolower($tipoMod);
                            if (isset($processedMods[$modKey])) {
                                continue;
                            }
                            $processedMods[$modKey] = true;

                            Detalle_pedido_Ingrediente::create([
                                'id_detalle_pedido' => $detalle->id_detalle_pedido,
                                'id_ingrediente' => $idIngrediente,
                                'tipo_modificacion' => $tipoMod,
                                'precio_aplicado' => $precioAplicado,
                            ]);
                        }
                    }
                }
            }

            $pedido->total = (int)$totalCalculado;
            $pedido->save();

            // Vincular venta con caja activa si nace pagado
            if ((int)($data['id_estado_pago'] ?? 1) === 2) {
                $cajaActiva = Caja::where('estado', 'abierta')->latest()->first();
                if ($cajaActiva) {
                    Venta::firstOrCreate([
                        'id_pedido' => $pedido->id_pedido,
                    ], [
                        'id_caja' => $cajaActiva->id_caja,
                    ]);
                }
            }

            return $this->getPedidoById($pedido->id_pedido);
        });
    }

    # Geters
    public function getAllPedidos()
    {
        return Pedido::with([
            'detalles.producto',
            'detalles.tamano',
            'detalles.tamaño',
            'detalles.ingredientes.ingrediente',
            'estadoPedido',
            'estadoPago',
            'usuario',
        ])->orderBy('id_pedido', 'desc')->get();
    }

    public function getPedidoById($id)
    {
        return $this->getPedidoBySearch($id);
    }

    public function getPedidoBySearch($search)
    {
        $shiftStart = $this->getShiftStart();

        $relations = [
            'detalles.producto',
            'detalles.tamano',
            'detalles.tamaño',
            'detalles.ingredientes.ingrediente',
            'estadoPedido',
            'estadoPago',
            'usuario',
        ];

        // 1. Prioridad: Buscar por numero_pedido_dia de la jornada/horario actual
        $pedidoJornada = Pedido::with($relations)
            ->where('numero_pedido_dia', $search)
            ->where('created_at', '>=', $shiftStart)
            ->orderBy('id_pedido', 'desc')
            ->first();

        if ($pedidoJornada) {
            return $pedidoJornada;
        }

        // 2. Si el search es un ID general de pedido (ej: 36)
        if (is_numeric($search) && (int)$search > 0) {
            $pedidoById = Pedido::with($relations)->find((int)$search);
            if ($pedidoById) {
                return $pedidoById;
            }
        }

        // 3. Fallback: Buscar por numero_pedido_dia mas reciente en cualquier jornada anterior
        return Pedido::with($relations)
            ->where('numero_pedido_dia', $search)
            ->orderBy('id_pedido', 'desc')
            ->first();
    }

    public function getPedidosByUsuarioId($idUsuario, $fechaInicio = null, $fechaFin = null, $limit = 50)
    {
        $query = Pedido::with([
            'detalles.producto', 
            'detalles.tamano', 
            'detalles.tamaño',
            'detalles.ingredientes.ingrediente', 
            'estadoPedido', 
            'estadoPago'
        ])->where('id_usuario', $idUsuario);

        if (!empty($fechaInicio)) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if (!empty($fechaFin)) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        return $query->orderBy('created_at', 'desc')
            ->orderBy('id_pedido', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPedidosByEstadoId($idEstado)
    {
        return Pedido::with(['detalles.producto', 'detalles.tamano', 'detalles.tamaño', 'estadoPedido', 'estadoPago'])
            ->where('id_estado_pedido', $idEstado)
            ->orderBy('id_pedido', 'desc')
            ->get();
    }

    public function getPedidosByEstadoPago($estadoPago)
    {
        return Pedido::with(['detalles.producto', 'detalles.tamano', 'detalles.tamaño', 'estadoPedido', 'estadoPago'])
            ->where('id_estado_pago', $estadoPago)
            ->orderBy('id_pedido', 'desc')
            ->get();
    }

    public function getVentaByPedidoId($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            return $pedido->venta;
        }
        return null;
    }

    public function descontarIngredientesStock($pedido)
    {
        if ($pedido && $pedido->id_pedido) {
            app(\App\Services\PedidoService::class)->descontarInventario($pedido->id_pedido);
        }
    }

    # Seters
    public function updatePedido($id, $data)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            $pedido = $this->getPedidoBySearch($id);
        }
        if ($pedido) {
            if (isset($data['items']) && is_array($data['items'])) {
                $oldDetalles = Detalle_pedido::where('id_pedido', $pedido->id_pedido)->pluck('id_detalle_pedido');
                Detalle_pedido_Ingrediente::whereIn('id_detalle_pedido', $oldDetalles)->delete();
                Detalle_pedido::where('id_pedido', $pedido->id_pedido)->delete();

                $totalCalculadoUpdate = 0;

                foreach ($data['items'] as $item) {
                    $idProducto = $item['id_producto'] ?? $item['catalogId'] ?? $item['id'] ?? null;
                    $idTamaño = $item['id_tamaño'] ?? $item['tamano_id'] ?? null;
                    $cantidad = max(1, is_numeric($item['cantidad'] ?? ($item['quantity'] ?? 1)) ? (int)($item['cantidad'] ?? ($item['quantity'] ?? 1)) : 1);

                    if (!$idTamaño && !empty($item['format'])) {
                        $tamObj = Tamaño::where('nombre', $item['format'])->first();
                        if ($tamObj) {
                            $idTamaño = $tamObj->id_tamaño;
                        }
                    }

                    if ($idProducto) {
                        $productoModel = Producto::find($idProducto);
                        $precioBase = 0;
                        if ($productoModel) {
                            $tamanoPrecio = Producto_Tamaño::where('id_producto', $idProducto)
                                ->where('id_tamaño', $idTamaño)
                                ->value('precio');

                            if ($tamanoPrecio !== null && (float)$tamanoPrecio > 0) {
                                $precioBase = (float)$tamanoPrecio;
                            } else {
                                $primerPrecio = Producto_Tamaño::where('id_producto', $idProducto)->value('precio');
                                $precioBase = $primerPrecio ? (float)$primerPrecio : (float)($productoModel->precio ?? $productoModel->precio_base ?? 0);
                            }
                        }

                        if ($precioBase <= 0 && isset($item['precio_unitario']) && is_numeric($item['precio_unitario'])) {
                            $precioBase = (float)$item['precio_unitario'];
                        }

                        $addedList = $item['addedExtras'] ?? ($item['agregados'] ?? ($item['agregadosDetails'] ?? []));
                        $numExtras = is_array($addedList) ? count($addedList) : 0;
                        $incluidos = (int)($productoModel->cantidad_incluida ?? 0);
                        $precioExtra = (float)($productoModel->precio_ingrediente_extra ?? 0);
                        $extrasCobrables = max(0, $numExtras - $incluidos);
                        $costoExtras = $extrasCobrables * $precioExtra;

                        $precioUnitarioFinal = $precioBase + $costoExtras;
                        $totalCalculadoUpdate += ($precioUnitarioFinal * $cantidad);

                        $detalle = Detalle_pedido::create([
                            'id_pedido' => $pedido->id_pedido,
                            'id_producto' => $idProducto,
                            'id_tamaño' => $idTamaño,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitarioFinal,
                        ]);

                        $removedList = $item['removedIngredients'] ?? ($item['excluidos'] ?? ($item['excluidosDetails'] ?? []));
                        $seenRemoved = [];
                        foreach ($removedList as $rem) {
                            $remId = is_array($rem) ? ($rem['id_ingrediente'] ?? $rem['id'] ?? null) : (is_numeric($rem) ? (int)$rem : null);
                            if (!$remId) {
                                $remName = is_array($rem) ? ($rem['nombre'] ?? $rem['ingrediente'] ?? '') : $rem;
                                if ($remName && is_string($remName)) {
                                    $remId = $this->resolverIdIngredienteDesdeNombre($remName);
                                }
                            }

                            if ($remId && !isset($seenRemoved[$remId])) {
                                $seenRemoved[$remId] = true;
                                Detalle_pedido_Ingrediente::create([
                                    'id_detalle_pedido' => $detalle->id_detalle_pedido,
                                    'id_ingrediente' => $remId,
                                    'tipo_modificacion' => 'Exclusión',
                                    'precio_aplicado' => 0,
                                ]);
                            }
                        }

                        $seenAdded = [];
                        foreach ($addedList as $ext) {
                            $extId = is_array($ext) ? ($ext['id_ingrediente'] ?? $ext['id'] ?? null) : (is_numeric($ext) ? (int)$ext : null);
                            if (!$extId) {
                                $extName = is_array($ext) ? ($ext['name'] ?? $ext['nombre'] ?? $ext['ingrediente'] ?? '') : $ext;
                                if ($extName && is_string($extName)) {
                                    $extId = $this->resolverIdIngredienteDesdeNombre($extName);
                                }
                            }

                            if ($extId && !isset($seenAdded[$extId])) {
                                $seenAdded[$extId] = true;
                                Detalle_pedido_Ingrediente::create([
                                    'id_detalle_pedido' => $detalle->id_detalle_pedido,
                                    'id_ingrediente' => $extId,
                                    'tipo_modificacion' => 'Agregado',
                                    'precio_aplicado' => is_array($ext) ? ($ext['precio'] ?? $ext['price'] ?? 0) : 0,
                                ]);
                            }
                        }
                    }
                }
                $data['total'] = (int)$totalCalculadoUpdate;
                unset($data['items']);
            }

            // Auto-asignar fecha_de_pago cuando se marca como Pagado (id_estado_pago = 2) y vincular venta
            if (isset($data['id_estado_pago']) && (int)$data['id_estado_pago'] === 2) {
                if (!$pedido->fecha_de_pago) {
                    $data['fecha_de_pago'] = now()->format('Y-m-d H:i:s');
                }
                $cajaActiva = Caja::where('estado', 'abierta')->latest()->first();
                if ($cajaActiva) {
                    Venta::firstOrCreate([
                        'id_pedido' => $pedido->id_pedido,
                    ], [
                        'id_caja' => $cajaActiva->id_caja,
                    ]);
                }
            }

            $pedido->update($data);

            return $this->getPedidoById($pedido->id_pedido);
        }
        return null;
    }

    # Delete
    public function deletePedidoById($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->delete();
            return true;
        }
        return false;
    }
}