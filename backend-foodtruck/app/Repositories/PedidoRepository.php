<?php

namespace App\Repositories;

use App\Models\Detalle_Pedido;
use App\Models\Detalle_pedido_Ingrediente;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

# Repositorio Pedido
class PedidoRepository
{
    public function getShiftStart()
    {
        $now = now();
        $dayOfWeek = (int)$now->format('w'); // 0 (Domingo) a 6 (Sábado)

        // 1. Obtener horario de ayer para verificar si la madrugada actual pertenece a la jornada de ayer
        $yesterdayDay = ($dayOfWeek + 6) % 7;
        $horarioAyer = \App\Models\Horario_atencion::where('dia_semana', $yesterdayDay)
            ->where('activo', true)
            ->first();

        // Calcular hora límite de cierre de la jornada de ayer (hora_cierre + minuto_colchon)
        $horaAyerApertura = $horarioAyer ? $horarioAyer->hora_apertura : '19:00:00';
        $horaAyerCierre = $horarioAyer ? $horarioAyer->hora_cierre : '01:30:00';
        $minColchonAyer = $horarioAyer ? (int)($horarioAyer->minuto_colchon ?? 30) : 30;

        $partsAyerAp = explode(':', $horaAyerApertura);
        $partsAyerCi = explode(':', $horaAyerCierre);

        $yesterdayShiftStart = now()->subDay()->setTime((int)$partsAyerAp[0], (int)($partsAyerAp[1] ?? 0), 0);
        $yesterdayShiftEnd = now()->subDay()
            ->setTime((int)$partsAyerCi[0], (int)($partsAyerCi[1] ?? 0), 0);
            
        // Si hora_cierre es de madrugada (menor a la apertura, ej. 01:30 < 19:00), el cierre ocurre al día siguiente
        if ((int)$partsAyerCi[0] < (int)$partsAyerAp[0]) {
            $yesterdayShiftEnd->addDay();
        }
        // Sumar los minutos de colchón al cierre de la jornada
        $yesterdayShiftEnd->addMinutes($minColchonAyer);

        // Si la hora actual es antes o igual al cierre con colchón de la jornada de ayer, estamos en la jornada de ayer
        if ($now->lessThanOrEqualTo($yesterdayShiftEnd)) {
            return $yesterdayShiftStart;
        }

        // 2. Obtener horario de hoy
        $horarioHoy = \App\Models\Horario_atencion::where('dia_semana', $dayOfWeek)
            ->where('activo', true)
            ->first();

        $horaHoyApertura = $horarioHoy ? $horarioHoy->hora_apertura : '19:00:00';
        $partsHoyAp = explode(':', $horaHoyApertura);
        $todayShiftStart = now()->setTime((int)$partsHoyAp[0], (int)($partsHoyAp[1] ?? 0), 0);

        if ($now->greaterThanOrEqualTo($todayShiftStart)) {
            return $todayShiftStart;
        }

        // Si estamos entre el cierre con colchón de ayer y la apertura de hoy, la última jornada fue la de ayer
        return $yesterdayShiftStart;
    }

    # Create
    public function createPedido($data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Calcular numero_pedido_dia correlativo para la jornada/turno actual (Horario_atencion BDD)
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

            // 2. Preparar campos de la cabecera del pedido
            $pedidoData = [
                'id_estado_pedido' => $data['id_estado_pedido'] ?? 1, // 1 = Pendiente
                'id_estado_pago' => $data['id_estado_pago'] ?? 1,   // 1 = Pendiente
                'id_usuario' => $data['id_usuario'] ?? null,        // Null para clientes QR anónimos
                'numero_pedido_dia' => $numeroPedidoDia,
                'nombre_persona' => $data['nombre_persona'] ?? ($data['persona_recibe'] ?? 'Cliente'),
                'numero_telefono' => $data['numero_telefono'] ?? ($data['telefono'] ?? ''),
                'metodo_pago' => $data['metodo_pago'] ?? 'Efectivo',
                'fecha' => now(),
                'total' => $totalCalculado,
                'notas' => $data['notas'] ?? null,
            ];

            $pedido = Pedido::create($pedidoData);

            // 3. Guardar detalles si vienen en el payload (items / detalles)
            foreach ($items as $item) {
                $idProducto = $item['id_producto'] ?? ($item['id'] ?? null);
                $idTamaño = $item['id_tamaño'] ?? ($item['id_tamano'] ?? 1); // 1 = Único por defecto
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

                    // Ingredientes personalizados, exclusiones o agregados
                    $allMods = [];

                    // 1. Modificaciones o ingredientes explícitos
                    $rawMods = $item['modificaciones'] ?? ($item['ingredientes'] ?? []);
                    if (is_array($rawMods)) {
                        foreach ($rawMods as $m) {
                            $allMods[] = $m;
                        }
                    }

                    // 2. Exclusiones directas (excluidos / ingredientes_excluidos / removedIngredients)
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

                    // 3. Agregados directos (agregados / addedExtras)
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

                    // 4. Guardar cada modificación en la base de datos (evitando duplicados)
                    $processedMods = [];
                    foreach ($allMods as $mod) {
                        $tipoMod = is_array($mod) ? ($mod['tipo'] ?? $mod['tipo_modificacion'] ?? 'Exclusión') : 'Exclusión';
                        $precioAplicado = is_array($mod) ? ($mod['precio'] ?? $mod['precio_aplicado'] ?? 0) : 0;
                        $idIngrediente = is_array($mod) ? ($mod['id_ingrediente'] ?? $mod['id'] ?? null) : (is_numeric($mod) ? (int)$mod : null);

                        // Si no hay ID numérico pero hay nombre, buscar por nombre en BDD
                        if (!$idIngrediente) {
                            $rawName = is_array($mod) ? ($mod['ingrediente'] ?? $mod['nombre'] ?? $mod['name'] ?? null) : $mod;
                            if ($rawName && is_string($rawName)) {
                                $cleanName = trim($rawName);
                                $foundIng = \App\Models\Ingrediente::where('nombre', $cleanName)
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
                                continue; // Evitar duplicar el mismo ingrediente y tipo de modificación
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
            'detalles.tamaño',
            'detalles.ingredientes.ingrediente',
            'estadoPedido',
            'estadoPago',
            'usuario',
        ];

        // 1. Buscar por numero_pedido_dia de la jornada actual
        $pedidoJornada = Pedido::with($relations)
            ->where('numero_pedido_dia', $search)
            ->where('created_at', '>=', $shiftStart)
            ->orderBy('id_pedido', 'desc')
            ->first();

        if ($pedidoJornada) {
            return $pedidoJornada;
        }

        // 2. Buscar por numero_pedido_dia mas reciente
        $pedidoDia = Pedido::with($relations)
            ->where('numero_pedido_dia', $search)
            ->orderBy('id_pedido', 'desc')
            ->first();

        if ($pedidoDia) {
            return $pedidoDia;
        }

        // 3. Fallback: Buscar por clave primaria id_pedido
        return Pedido::with($relations)->find($search);
    }

    public function getPedidosByUsuarioId($idUsuario)
    {
        return Pedido::with(['detalles.producto', 'estadoPedido', 'estadoPago'])
            ->where('id_usuario', $idUsuario)
            ->orderBy('id_pedido', 'desc')
            ->get();
    }

    public function getPedidosByEstadoId($idEstado)
    {
        return Pedido::with(['detalles.producto', 'estadoPedido', 'estadoPago'])
            ->where('id_estado_pedido', $idEstado)
            ->orderBy('id_pedido', 'desc')
            ->get();
    }

    public function getPedidosByEstadoPago($estadoPago)
    {
        return Pedido::with(['detalles.producto', 'estadoPedido', 'estadoPago'])
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

            // Obtener exclusiones para este detalle
            $excluidosIds = $detalle->ingredientes
                ->filter(fn($mod) => strtolower($mod->tipo_modificacion) === 'exclusión' || strtolower($mod->tipo_modificacion) === 'exclusion')
                ->pluck('id_ingrediente')
                ->filter()
                ->toArray();

            // Buscar receta base del producto
            $receta = \App\Models\Producto_ingrediente::where('id_producto', $idProducto)
                ->when($idTamaño, fn($q) => $q->where(function($query) use ($idTamaño) {
                    $query->where('id_tamaño', $idTamaño)->orWhereNull('id_tamaño');
                }))
                ->get();

            foreach ($receta as $itemReceta) {
                if (!in_array($itemReceta->id_ingrediente, $excluidosIds)) {
                    $cantRequerida = ($itemReceta->cantidad ?? 1) * $cantidadPedido;

                    $ingrediente = \App\Models\Ingrediente::find($itemReceta->id_ingrediente);
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
            $previousEstado = (int)$pedido->id_estado_pedido;

            // Si vienen items/detalles para actualizar los productos del pedido en BDD
            if (isset($data['items']) && is_array($data['items'])) {
                // Eliminar detalles e ingredientes asociados anteriores
                $oldDetalles = Detalle_Pedido::where('id_pedido', $pedido->id_pedido)->pluck('id_detalle_pedido');
                Detalle_pedido_Ingrediente::whereIn('id_detalle_pedido', $oldDetalles)->delete();
                Detalle_Pedido::where('id_pedido', $pedido->id_pedido)->delete();

                foreach ($data['items'] as $item) {
                    $idProducto = $item['id_producto'] ?? $item['catalogId'] ?? $item['id'] ?? null;
                    $idTamaño = $item['id_tamaño'] ?? $item['tamano_id'] ?? null;
                    $cantidad = $item['cantidad'] ?? $item['quantity'] ?? 1;
                    $precioUnitario = $item['precio_unitario'] ?? ($item['subtotal'] && $cantidad ? $item['subtotal'] / $cantidad : 0);

                    // Si no viene id_tamaño directamente, intentar resolverlo por el nombre del formato (ej: Chico, Grande, XXL)
                    if (!$idTamaño && !empty($item['format'])) {
                        $tamObj = \App\Models\Tamaño::where('nombre', $item['format'])->first();
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

                        // Exclusiones de ingredientes (sin duplicados)
                        $removedList = $item['removedIngredients'] ?? ($item['excluidos'] ?? []);
                        $seenRemoved = [];
                        foreach ($removedList as $rem) {
                            $remName = is_array($rem) ? ($rem['nombre'] ?? $rem['ingrediente'] ?? '') : $rem;
                            if (!$remName || isset($seenRemoved[$remName])) continue;
                            $seenRemoved[$remName] = true;

                            $ingObj = \App\Models\Ingrediente::where('nombre', $remName)->first();
                            if ($ingObj) {
                                Detalle_pedido_Ingrediente::create([
                                    'id_detalle_pedido' => $detalle->id_detalle_pedido,
                                    'id_ingrediente' => $ingObj->id_ingrediente,
                                    'tipo_modificacion' => 'Exclusión',
                                    'precio_aplicado' => 0,
                                ]);
                            }
                        }

                        // Agregados / Extras de ingredientes (sin duplicados)
                        $addedList = $item['addedExtras'] ?? ($item['agregados'] ?? []);
                        $seenAdded = [];
                        foreach ($addedList as $ext) {
                            $extName = is_array($ext) ? ($ext['name'] ?? $ext['nombre'] ?? '') : $ext;
                            if (!$extName || isset($seenAdded[$extName])) continue;
                            $seenAdded[$extName] = true;

                            $ingObj = \App\Models\Ingrediente::where('nombre', $extName)->first();
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