<?php

namespace App\Repositories;

use App\Models\Detalle_Pedido;
use App\Models\Detalle_pedido_Ingrediente;
use App\Models\Pedido;
use App\Models\Horario_atencion;
use App\Models\Ingrediente;
use App\Models\Producto_ingrediente;
use App\Models\Tamaño;
use Illuminate\Support\Facades\DB;

# Repositorio Pedido
class PedidoRepository
{
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

            $totalCalculado = isset($data['total']) ? (float)$data['total'] : 0;
            if ($totalCalculado <= 0 && !empty($items)) {
                foreach ($items as $it) {
                    $cant = $it['cantidad'] ?? ($it['quantity'] ?? 1);
                    $pu = $it['precio_unitario'] ?? ($it['precio'] ?? ($it['subtotal'] && $cant ? $it['subtotal'] / $cant : 0));
                    $totalCalculado += ($cant * $pu);
                }
            }

            $pedidoData = [
                'id_estado_pedido' => $data['id_estado_pedido'] ?? 1,
                'id_estado_pago' => $data['id_estado_pago'] ?? 1,
                'id_usuario' => $data['id_usuario'] ?? null,
                'numero_pedido_dia' => $numeroPedidoDia,
                'nombre_persona' => $data['nombre_persona'] ?? ($data['persona_recibe'] ?? 'Cliente'),
                'numero_telefono' => $data['numero_telefono'] ?? ($data['telefono'] ?? ''),
                'metodo_pago' => $data['metodo_pago'] ?? 'Efectivo',
                'fecha' => now(),
                'total' => $totalCalculado,
                'notas' => $data['notas'] ?? null,
            ];

            $pedido = Pedido::create($pedidoData);

            foreach ($items as $item) {
                $idProducto = $item['id_producto'] ?? ($item['id'] ?? null);
                $idTamaño = $item['id_tamaño'] ?? ($item['id_tamano'] ?? 1);
                $cantidad = $item['cantidad'] ?? ($item['quantity'] ?? 1);
                $precioUnitario = $item['precio_unitario'] ?? ($item['precio'] ?? 0);

                if ($idProducto) {
                    $detalle = Detalle_Pedido::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_producto' => $idProducto,
                        'id_tamaño' => $idTamaño,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                    ]);

                    $allMods = [];

                    $rawMods = $item['modificaciones'] ?? ($item['ingredientes'] ?? []);
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

                    $rawAgregados = $item['agregados'] ?? ($item['addedExtras'] ?? []);
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
                        $tipoMod = is_array($mod) ? ($mod['tipo'] ?? $mod['tipo_modificacion'] ?? 'Exclusión') : 'Exclusión';
                        $precioAplicado = is_array($mod) ? ($mod['precio'] ?? $mod['precio_aplicado'] ?? 0) : 0;
                        $idIngrediente = is_array($mod) ? ($mod['id_ingrediente'] ?? $mod['id'] ?? null) : (is_numeric($mod) ? (int)$mod : null);

                        if (!$idIngrediente) {
                            $rawName = is_array($mod) ? ($mod['ingrediente'] ?? $mod['nombre'] ?? $mod['name'] ?? null) : $mod;
                            if ($rawName && is_string($rawName)) {
                                $cleanName = trim($rawName);
                                $foundIng = Ingrediente::where('nombre', $cleanName)
                                    ->orWhere('nombre', 'LIKE', '%' . $cleanName . '%')
                                    ->first();
                                if ($foundIng) {
                                    $idIngrediente = $foundIng->id_ingrediente;
                                }
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
        $pedido->load(['detalles.ingredientes']);

        foreach ($pedido->detalles as $detalle) {
            $idProducto = $detalle->id_producto;
            $idTamaño = $detalle->id_tamaño;
            $cantidadPedido = $detalle->cantidad;

            $excluidosIds = $detalle->ingredientes
                ->filter(fn($mod) => strtolower($mod->tipo_modificacion) === 'exclusión' || strtolower($mod->tipo_modificacion) === 'exclusion')
                ->pluck('id_ingrediente')
                ->filter()
                ->toArray();

            $receta = Producto_ingrediente::where('id_producto', $idProducto)
                ->when($idTamaño, fn($q) => $q->where(function($query) use ($idTamaño) {
                    $query->where('id_tamaño', $idTamaño)->orWhereNull('id_tamaño');
                }))
                ->get();

            foreach ($receta as $itemReceta) {
                if (!in_array($itemReceta->id_ingrediente, $excluidosIds)) {
                    $cantRequerida = ($itemReceta->cantidad ?? 1) * $cantidadPedido;

                    $ingrediente = Ingrediente::find($itemReceta->id_ingrediente);
                    if ($ingrediente) {
                        $nuevoStock = max(0, $ingrediente->cantidad_actual - $cantRequerida);
                        $ingrediente->update([
                            'cantidad_actual' => $nuevoStock,
                            'disponible' => $nuevoStock > 0
                        ]);
                    }
                }
            }
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
                $oldDetalles = Detalle_Pedido::where('id_pedido', $pedido->id_pedido)->pluck('id_detalle_pedido');
                Detalle_pedido_Ingrediente::whereIn('id_detalle_pedido', $oldDetalles)->delete();
                Detalle_Pedido::where('id_pedido', $pedido->id_pedido)->delete();

                foreach ($data['items'] as $item) {
                    $idProducto = $item['id_producto'] ?? $item['catalogId'] ?? $item['id'] ?? null;
                    $idTamaño = $item['id_tamaño'] ?? $item['tamano_id'] ?? null;
                    $cantidad = $item['cantidad'] ?? $item['quantity'] ?? 1;
                    $precioUnitario = $item['precio_unitario'] ?? ($item['subtotal'] && $cantidad ? $item['subtotal'] / $cantidad : 0);

                    if (!$idTamaño && !empty($item['format'])) {
                        $tamObj = Tamaño::where('nombre', $item['format'])->first();
                        if ($tamObj) {
                            $idTamaño = $tamObj->id_tamaño;
                        }
                    }

                    if ($idProducto) {
                        $detalle = Detalle_Pedido::create([
                            'id_pedido' => $pedido->id_pedido,
                            'id_producto' => $idProducto,
                            'id_tamaño' => $idTamaño,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitario,
                        ]);

                        $removedList = $item['removedIngredients'] ?? ($item['excluidos'] ?? []);
                        $seenRemoved = [];
                        foreach ($removedList as $rem) {
                            $remName = is_array($rem) ? ($rem['nombre'] ?? $rem['ingrediente'] ?? '') : $rem;
                            if (!$remName || isset($seenRemoved[$remName])) continue;
                            $seenRemoved[$remName] = true;

                            $ingObj = Ingrediente::where('nombre', $remName)->first();
                            if ($ingObj) {
                                Detalle_pedido_Ingrediente::create([
                                    'id_detalle_pedido' => $detalle->id_detalle_pedido,
                                    'id_ingrediente' => $ingObj->id_ingrediente,
                                    'tipo_modificacion' => 'Exclusión',
                                    'precio_aplicado' => 0,
                                    ]);
                            }
                        }

                        $addedList = $item['addedExtras'] ?? ($item['agregados'] ?? []);
                        $seenAdded = [];
                        foreach ($addedList as $ext) {
                            $extName = is_array($ext) ? ($ext['name'] ?? $ext['nombre'] ?? '') : $ext;
                            if (!$extName || isset($seenAdded[$extName])) continue;
                            $seenAdded[$extName] = true;

                            $ingObj = Ingrediente::where('nombre', $extName)->first();
                            if ($ingObj) {
                                Detalle_pedido_Ingrediente::create([
                                    'id_detalle_pedido' => $detalle->id_detalle_pedido,
                                    'id_ingrediente' => $ingObj->id_ingrediente,
                                    'tipo_modificacion' => 'Agregado',
                                    'precio_aplicado' => 0,
                                ]);
                            }
                        }
                    }
                }
                unset($data['items']);
            }

            // Auto-asignar fecha_de_pago cuando se marca como Pagado (id_estado_pago = 2)
            if (isset($data['id_estado_pago']) && (int)$data['id_estado_pago'] === 2 && !$pedido->fecha_de_pago) {
                $data['fecha_de_pago'] = now()->format('Y-m-d H:i:s');
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