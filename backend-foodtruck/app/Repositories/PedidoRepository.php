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
    /**
     * Cache en memoria por instancia para evitar N+1 al resolver ingredientes por nombre.
     */
    protected ?\Illuminate\Support\Collection $ingredientesCache = null;

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

        if ($this->ingredientesCache === null) {
            $this->ingredientesCache = Ingrediente::all();
        }
        $todosIngredientes = $this->ingredientesCache;

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

    /**
     * Determina si un ingrediente corresponde a los aderezos gratuitos de fajitas/hamburguesas.
     */
    protected function isAderezoGratuito(?string $nombre): bool
    {
        $norm = $this->normalizeIngredienteNombre($nombre);
        if (!$norm) {
            return false;
        }
        return in_array($norm, [
            'ketchup',
            'mostaza',
            'salsa bbq',
            'bbq',
            'mayonesa de ajo',
            'salsa de queso',
            'salsa de aji',
        ], true);
    }

    /**
     * Verifica si el producto habilita selector gratuito de aderezos (Hamburguesas y Fajitas).
     */
    protected function admiteAderezosGratuitos(?Producto $producto): bool
    {
        if (!$producto) {
            return false;
        }
        $catNombre = mb_strtolower((string)($producto->categoria->nombre_categoria ?? ''), 'UTF-8');
        $prodNombre = mb_strtolower((string)($producto->nombre ?? ''), 'UTF-8');
        return str_contains($catNombre, 'hamburguesa')
            || str_contains($catNombre, 'fajita')
            || str_contains($prodNombre, 'hamburguesa')
            || str_contains($prodNombre, 'fajita');
    }

    /**
     * Resuelve el precio base considerando promociones vigentes por producto y tamaño.
     */
    protected function resolverPrecioConPromocion(?Producto $producto, ?int $idTamaño, float $precioBase): float
    {
        if (!$producto) {
            return $precioBase;
        }

        $todayStr = now()->format('Y-m-d');
        $window = $this->getShiftWindow();
        $shiftDateStr = $window['shift_date'] ?? $todayStr;

        $promos = $producto->relationLoaded('promocionesActivas')
            ? $producto->promocionesActivas
            : $producto->promocionesActivas()->get();

        $vigentes = $promos->filter(function ($promo) use ($todayStr, $shiftDateStr) {
            if (empty($promo->activo)) {
                return false;
            }
            $inicio = $promo->fecha_inicio ? $promo->fecha_inicio->format('Y-m-d') : '';
            $fin = $promo->fecha_fin ? $promo->fecha_fin->format('Y-m-d') : '';
            $matchesToday = (!$inicio || $inicio <= $todayStr) && (!$fin || $fin >= $todayStr);
            $matchesShift = (!$inicio || $inicio <= $shiftDateStr) && (!$fin || $fin >= $shiftDateStr);
            return $matchesToday || $matchesShift;
        });

        // Prioridad: promoción específica para ese tamaño > promoción general (id_tamaño === null)
        $promoAplicable = $vigentes->first(function ($promo) use ($idTamaño) {
            return $idTamaño && $promo->id_tamaño !== null && (int)$promo->id_tamaño === (int)$idTamaño;
        }) ?? $vigentes->first(function ($promo) {
            return $promo->id_tamaño === null;
        });

        if ($promoAplicable && (float)$promoAplicable->precio_promocional > 0) {
            $promoPrecio = (float)$promoAplicable->precio_promocional;
            return $precioBase > 0 ? min($promoPrecio, $precioBase) : $promoPrecio;
        }

        return $precioBase;
    }

    /**
     * Calcula el costo adicional real de los ingredientes agregados y devuelve también
     * el precio aplicado por id_ingrediente (soporta precio_extra por tamaño y aderezos gratuitos).
     */
    public function calcularCostoExtras(?Producto $producto, array $rawAgregados, ?int $idTamaño = null): array
    {
        if (!$producto || empty($rawAgregados)) {
            return ['total' => 0.0, 'precios_por_id' => []];
        }

        $piPorId = [];
        $piPorNombre = [];
        $rank = static function ($pi) use ($idTamaño): int {
            $sizePriority = 1;
            if ($idTamaño !== null) {
                if ((int)($pi->id_tamaño ?? 0) === $idTamaño) {
                    $sizePriority = 3;
                } elseif ($pi->id_tamaño === null) {
                    $sizePriority = 2;
                }
            } else {
                $sizePriority = $pi->id_tamaño === null ? 2 : 1;
            }
            return ($sizePriority * 10) + ($pi->precio_extra !== null ? 2 : 0) + ($pi->incluido_por_defecto === false ? 1 : 0);
        };

        foreach ($producto->ingredientes ?? [] as $pi) {
            if ($pi->id_ingrediente) {
                if (!isset($piPorId[$pi->id_ingrediente]) || $rank($pi) > $rank($piPorId[$pi->id_ingrediente])) {
                    $piPorId[$pi->id_ingrediente] = $pi;
                }
            }
            $norm = $this->normalizeIngredienteNombre($pi->ingrediente->nombre ?? '');
            if ($norm && (!isset($piPorNombre[$norm]) || $rank($pi) > $rank($piPorNombre[$norm]))) {
                $piPorNombre[$norm] = $pi;
            }
        }

        $permiteAderezos = $this->admiteAderezosGratuitos($producto);
        $incluidos = (int)($producto->cantidad_incluida ?? 0);
        $precioExtraDefault = (float)($producto->precio_ingrediente_extra ?? 0);

        $cobrables = [];
        $preciosPorId = [];

        foreach ($rawAgregados as $ag) {
            $ingNombre = is_array($ag) ? ($ag['nombre'] ?? $ag['name'] ?? $ag['ingrediente'] ?? '') : (is_string($ag) ? $ag : '');
            $ingId = is_array($ag) ? ($ag['id_ingrediente'] ?? $ag['id'] ?? null) : (is_numeric($ag) ? (int)$ag : null);

            if (!$ingId && $ingNombre) {
                $ingId = $this->resolverIdIngredienteDesdeNombre($ingNombre);
            }

            if ($permiteAderezos && $this->isAderezoGratuito($ingNombre)) {
                if ($ingId) {
                    $preciosPorId[$ingId] = 0;
                }
                continue;
            }

            $pi = ($ingId && isset($piPorId[$ingId])) ? $piPorId[$ingId] : ($piPorNombre[$this->normalizeIngredienteNombre($ingNombre) ?? ''] ?? null);
            if (!$pi) {
                $label = $ingNombre ?: ('ID #' . ($ingId ?? '?'));
                throw new \InvalidArgumentException("El agregado \"{$label}\" no está habilitado para \"{$producto->nombre}\".");
            }

            if ($pi->ingrediente && $pi->ingrediente->disponible === false) {
                throw new \InvalidArgumentException("El ingrediente extra \"{$pi->ingrediente->nombre}\" no tiene stock disponible.");
            }

            $resolvedId = $ingId ?: $pi->id_ingrediente;
            $unitCost = ($pi->precio_extra !== null) ? (float)$pi->precio_extra : $precioExtraDefault;

            $cobrables[] = [
                'id' => $resolvedId,
                'cost' => $unitCost,
            ];
        }

        // Ordenar de menor a mayor costo para que los incluidos gratuitos cubran de manera
        // determinista los toppings base e impedir arbitraje por orden del arreglo enviado.
        usort($cobrables, static function ($a, $b) {
            return $a['cost'] <=> $b['cost'];
        });

        $totalExtras = 0.0;
        foreach ($cobrables as $idx => $entry) {
            // Los primeros N ingredientes no-aderezo están incluidos sin costo en productos personalizables
            $cobro = ($idx < $incluidos) ? 0.0 : $entry['cost'];
            $totalExtras += $cobro;
            if (!empty($entry['id'])) {
                $preciosPorId[$entry['id']] = $cobro;
            }
        }

        return [
            'total' => $totalExtras,
            'precios_por_id' => $preciosPorId,
        ];
    }

    public function getShiftWindow($referenceTime = null)
    {
        $now = $referenceTime ? \Carbon\Carbon::parse($referenceTime) : now();
        $dayOfWeek = (int)$now->format('w'); // 0 (Domingo) a 6 (Sábado)

        // 1. Horario de ayer (para madrugadas)
        $yesterdayDay = ($dayOfWeek + 6) % 7;
        $horarioAyer = Horario_atencion::where('dia_semana', $yesterdayDay)->first();

        if ($horarioAyer && $horarioAyer->activo) {
            $horaAyerAp = $horarioAyer->hora_apertura ?: '19:00:00';
            $horaAyerCi = $horarioAyer->hora_cierre ?: '00:30:00';
            $colchonAyer = (int)($horarioAyer->minuto_colchon ?? 30);

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
                    'is_day_off' => false,
                    'shift_date' => $yesterdayShiftStart->format('Y-m-d')
                ];
            }
        }

        // 2. Horario de hoy
        $horarioHoy = Horario_atencion::where('dia_semana', $dayOfWeek)->first();
        $isDiaActivo = $horarioHoy ? (bool)$horarioHoy->activo : false;

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

        // Si el día de hoy fue desactivado por el administrador:
        if (!$isDiaActivo) {
            return [
                'start' => $todayShiftStart,
                'end' => $todayShiftEndWithBuffer,
                'hora_apertura' => $horaHoyAp,
                'hora_cierre' => $horaHoyCi,
                'dia' => 'Cerrado hoy (Día de descanso)',
                'is_active' => false,
                'is_day_off' => true,
                'shift_date' => $todayShiftStart->format('Y-m-d')
            ];
        }

        // Si hoy está activo y estamos dentro de la ventana de atención:
        if ($now->greaterThanOrEqualTo($todayShiftStart) && $now->lessThanOrEqualTo($todayShiftEndWithBuffer)) {
            return [
                'start' => $todayShiftStart,
                'end' => $todayShiftEndWithBuffer,
                'hora_apertura' => $horaHoyAp,
                'hora_cierre' => $horaHoyCi,
                'dia' => 'Hoy',
                'is_active' => true,
                'is_day_off' => false,
                'shift_date' => $todayShiftStart->format('Y-m-d')
            ];
        }

        // 3. Durante el día antes de abrir:
        return [
            'start' => $todayShiftStart,
            'end' => $todayShiftEndWithBuffer,
            'hora_apertura' => $horaHoyAp,
            'hora_cierre' => $horaHoyCi,
            'dia' => 'Hoy (Abre a las ' . substr($horaHoyAp, 0, 5) . ')',
            'is_active' => false,
            'is_day_off' => false,
            'shift_date' => $todayShiftStart->format('Y-m-d')
        ];
    }

    public function getShiftStart()
    {
        $window = $this->getShiftWindow();
        return $window['start'];
    }

    /**
     * Inicio efectivo de la jornada operativa para numeración de comandas,
     * evitando que pedidos POS previos a las 19:00 colisionen con numero_pedido_dia = 1.
     */
    protected function getOrderNumberingStart(array $window): \Carbon\Carbon
    {
        $tz = config('app.timezone', 'America/Santiago');
        $now = now($tz);
        $shiftDate = $window['shift_date'] ?? $now->format('Y-m-d');
        $opStart = \Carbon\Carbon::parse($shiftDate . ' 06:00:00', $tz);

        if (!empty($window['is_active']) && !empty($window['start'])) {
            return $window['start']->copy()->min($opStart);
        }

        if ($now->hour < 6) {
            return $now->copy()->subDay()->setTime(6, 0, 0);
        }

        return $opStart;
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

        // Si no encontró pedido Y el turno de hoy aún no ha abierto (antes de apertura),
        // buscar también en el turno inmediatamente anterior (ayer por la noche).
        // Esto cubre el caso: cliente pide a las 21:00, revisa estado al día siguiente
        // a las 13:00 antes de que abra el local. Solo retrocede UN día, nunca más.
        if (!$pedido && !($window['is_active'] ?? false) && !($window['is_day_off'] ?? false)) {
            $referenceYesterday = $targetDate
                ? \Carbon\Carbon::parse($targetDate)->subDay()
                : now()->subDay();
            $windowAyer = $this->getShiftWindow($referenceYesterday);

            $pedidoAyer = Pedido::with($relations)
                ->where('numero_pedido_dia', (int)$numeroComanda)
                ->whereBetween('created_at', [$windowAyer['start'], $windowAyer['end']])
                ->orderBy('id_pedido', 'desc')
                ->first();

            if ($pedidoAyer) {
                $pedido = $pedidoAyer;
                $window = $windowAyer;
            }
        }

        return [
            'pedido' => $pedido,
            'jornada' => [
                'inicio' => $window['start']->format('Y-m-d H:i:s'),
                'fin' => $window['end']->format('Y-m-d H:i:s'),
                'hora_apertura' => substr($window['hora_apertura'] ?? '19:00', 0, 5),
                'hora_cierre' => substr($window['hora_cierre'] ?? '00:30', 0, 5),
                'es_jornada_activa' => (bool)($window['is_active'] ?? false),
                'es_dia_cerrado' => (bool)($window['is_day_off'] ?? false)
            ]
        ];
    }

    # Create
    public function createPedido($data)
    {
        return DB::transaction(function () use ($data) {
            $window = $this->getShiftWindow();
            if (!empty($window['is_day_off'])) {
                throw new \InvalidArgumentException('No es posible tomar pedidos: El día de hoy ha sido desactivado por administración (día de descanso).');
            }

            $shiftStart = $this->getOrderNumberingStart($window);
            $ultimoPedidoShift = Pedido::where('created_at', '>=', $shiftStart)
                ->orderBy('numero_pedido_dia', 'desc')
                ->lockForUpdate()
                ->first();
            $numeroPedidoDia = $ultimoPedidoShift ? ((int)$ultimoPedidoShift->numero_pedido_dia + 1) : 1;

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

                $rawTamano = $item['id_tamaño'] ?? ($item['id_tamano'] ?? ($item['id_size'] ?? null));
                if (is_string($rawTamano) && strpos($rawTamano, '_') !== false) {
                    $rawTamano = explode('_', $rawTamano)[0];
                }
                $idTamaño = is_numeric($rawTamano) ? (int)$rawTamano : null;

                $cantidad = max(1, is_numeric($item['cantidad'] ?? ($item['quantity'] ?? 1)) 
                    ? (int)($item['cantidad'] ?? ($item['quantity'] ?? 1)) 
                    : 1);

                if ($idProducto) {
                    $productoModel = Producto::with(['categoria', 'tamaños', 'ingredientes.ingrediente', 'promocionesActivas'])->find($idProducto);
                    if (!$productoModel) {
                        throw new \InvalidArgumentException("El producto ID #{$idProducto} no existe en el catálogo.");
                    }
                    if ($productoModel->activo === false || $productoModel->disponible === false) {
                        throw new \InvalidArgumentException("El producto \"{$productoModel->nombre}\" no está disponible en este momento.");
                    }

                    // 1. Recalcular precio base del producto desde la BD (validando tamaño y promoción vigente)
                    $precioBase = 0;
                    $tamanosDisponibles = $productoModel->tamaños ?? collect();
                    if ($tamanosDisponibles->isNotEmpty()) {
                        if ($idTamaño === null) {
                            $primerTamano = $tamanosDisponibles->sortBy('pivot.precio')->first();
                            $idTamaño = (int)$primerTamano->id_tamaño;
                            $tamanoRow = $primerTamano;
                        } else {
                            $tamanoRow = $tamanosDisponibles->firstWhere('id_tamaño', $idTamaño);
                            if (!$tamanoRow) {
                                throw new \InvalidArgumentException("El tamaño seleccionado no corresponde al producto \"{$productoModel->nombre}\".");
                            }
                        }
                        $precioBase = (float)($tamanoRow->pivot->precio ?? 0);
                    } else {
                        $idTamaño = $idTamaño ?? 1;
                        $precioBase = (float)($productoModel->precio ?? $productoModel->precio_base ?? 0);
                    }

                    $precioBase = $this->resolverPrecioConPromocion($productoModel, $idTamaño ? (int)$idTamaño : null, $precioBase);

                    // Blindaje de Seguridad: El precio debe provenir siempre del catálogo oficial de la BD
                    if ($precioBase <= 0) {
                        throw new \InvalidArgumentException("El producto \"{$productoModel->nombre}\" no tiene un precio válido asignado en el catálogo.");
                    }

                    // 2. Calcular costo de extras / agregados cobrables (soporta precio_extra por tamaño y aderezos gratuitos)
                    $rawAgregados = $item['agregados'] ?? ($item['addedExtras'] ?? ($item['agregadosDetails'] ?? []));
                    $extrasCalc = $this->calcularCostoExtras($productoModel, is_array($rawAgregados) ? $rawAgregados : [], $idTamaño ? (int)$idTamaño : null);
                    $costoExtras = $extrasCalc['total'];
                    $preciosExtrasPorId = $extrasCalc['precios_por_id'];

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
                            if ($tipoMod === 'Agregado' && !array_key_exists($idIngrediente, $preciosExtrasPorId)) {
                                // Ignorar agregados que no pasaron por la lista blanca y recálculo oficial de calcularCostoExtras
                                continue;
                            }

                            $modKey = $idIngrediente . '_' . strtolower($tipoMod);
                            if (isset($processedMods[$modKey])) {
                                continue;
                            }
                            $processedMods[$modKey] = true;

                            if ($tipoMod === 'Agregado') {
                                $precioAplicado = $preciosExtrasPorId[$idIngrediente];
                            }

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
    protected function buildAllPedidosQuery($fecha = null)
    {
        $query = Pedido::with([
            'detalles.producto',
            'detalles.tamano',
            'detalles.tamaño',
            'detalles.ingredientes.ingrediente',
            'estadoPedido',
            'estadoPago',
            'usuario',
        ]);

        if (!empty($fecha)) {
            $window = $this->getShiftWindow($fecha);
            $opStart = $this->getOrderNumberingStart($window);
            $query->whereBetween('created_at', [$opStart, $window['end']]);
        }

        return $query->orderBy('id_pedido', 'desc');
    }

    public function getAllPedidos($fecha = null, int $limit = 300)
    {
        $safeLimit = max(1, min(1000, $limit));
        return $this->buildAllPedidosQuery($fecha)
            ->limit($safeLimit)
            ->get();
    }

    public function paginatePedidos($fecha = null, int $perPage = 50, int $page = 1)
    {
        $safePerPage = max(1, min(200, $perPage));
        $safePage = max(1, $page);
        return $this->buildAllPedidosQuery($fecha)
            ->paginate($safePerPage, ['*'], 'page', $safePage);
    }

    public function getPedidoById($id)
    {
        return $this->getPedidoBySearch($id);
    }

    public function getPedidoBySearch($search)
    {
        if (!is_numeric($search) || (int)$search <= 0) {
            return null;
        }

        $num = (int)$search;
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

        // Consulta única priorizando: 1) comanda de la jornada actual, 2) ID primario, 3) comanda histórica
        return Pedido::with($relations)
            ->where(function ($q) use ($num) {
                $q->where('numero_pedido_dia', $num)
                  ->orWhere('id_pedido', $num);
            })
            ->orderByRaw(
                'CASE WHEN numero_pedido_dia = ? AND created_at >= ? THEN 0 WHEN id_pedido = ? THEN 1 ELSE 2 END, id_pedido DESC',
                [$num, $shiftStart, $num]
            )
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
        return DB::transaction(function () use ($id, $data) {
            $pedido = Pedido::lockForUpdate()->find($id);
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
                            $productoModel = Producto::with(['categoria', 'tamaños', 'ingredientes.ingrediente', 'promocionesActivas'])->find($idProducto);
                            if (!$productoModel) {
                                throw new \InvalidArgumentException("El producto ID #{$idProducto} no existe en el catálogo.");
                            }
                            if ($productoModel->activo === false) {
                                throw new \InvalidArgumentException("El producto \"{$productoModel->nombre}\" está inactivo.");
                            }

                            $precioBase = 0;
                            $tamanosDisponibles = $productoModel->tamaños ?? collect();
                            if ($tamanosDisponibles->isNotEmpty()) {
                                if (!$idTamaño) {
                                    $primerTamano = $tamanosDisponibles->sortBy('pivot.precio')->first();
                                    $idTamaño = (int)$primerTamano->id_tamaño;
                                    $tamanoRow = $primerTamano;
                                } else {
                                    $tamanoRow = $tamanosDisponibles->firstWhere('id_tamaño', (int)$idTamaño);
                                    if (!$tamanoRow) {
                                        throw new \InvalidArgumentException("El tamaño seleccionado no corresponde al producto \"{$productoModel->nombre}\".");
                                    }
                                }
                                $precioBase = (float)($tamanoRow->pivot->precio ?? 0);
                            } else {
                                $idTamaño = $idTamaño ?? 1;
                                $precioBase = (float)($productoModel->precio ?? $productoModel->precio_base ?? 0);
                            }

                            $precioBase = $this->resolverPrecioConPromocion($productoModel, $idTamaño ? (int)$idTamaño : null, $precioBase);

                            // Blindaje de Seguridad: Prevenir manipulación de precio en edición
                            if ($precioBase <= 0) {
                                throw new \InvalidArgumentException("El producto \"{$productoModel->nombre}\" no tiene un precio válido asignado en el catálogo.");
                            }

                            $addedList = $item['addedExtras'] ?? ($item['agregados'] ?? ($item['agregadosDetails'] ?? []));
                            $extrasCalc = $this->calcularCostoExtras($productoModel, is_array($addedList) ? $addedList : [], $idTamaño ? (int)$idTamaño : null);
                            $costoExtras = $extrasCalc['total'];
                            $preciosExtrasPorId = $extrasCalc['precios_por_id'];

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
                                        'precio_aplicado' => $preciosExtrasPorId[$extId] ?? (is_array($ext) ? ($ext['precio'] ?? $ext['price'] ?? 0) : 0),
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
        });
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