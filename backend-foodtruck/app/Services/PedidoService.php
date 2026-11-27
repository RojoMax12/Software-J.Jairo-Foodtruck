<?php

namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Models\Ingrediente;
use App\Models\Movimientos;
use App\Models\Pedido;
use App\Models\Producto_ingrediente;
use App\Repositories\PedidoRepository;
use App\Support\CatalogCache;
use Illuminate\Support\Facades\DB;

# Servicio Pedido
class PedidoService
{
    protected $pedidoRepository;

    private function normalizarTipoModificacion($tipo)
    {
        if ($tipo === null) {
            return '';
        }

        $texto = strtolower(trim((string)$tipo));
        if (str_contains($texto, 'exclu') || str_contains($texto, 'quit') || str_contains($texto, 'sin ') || str_contains($texto, 'elimin')) {
            return 'Exclusión';
        }

        if (str_contains($texto, 'agre') || str_contains($texto, 'extra') || str_contains($texto, 'adicio') || str_contains($texto, 'añadi') || str_contains($texto, 'agrega') || str_contains($texto, 'con ')) {
            return 'Agregado';
        }

        return 'Agregado';
    }

    private function normalizarNombreIngrediente($valor)
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $texto = trim((string)$valor);
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = mb_strtolower($texto, 'UTF-8');
        return strtr($texto, [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
            'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
            'ñ'=>'n', 'Ñ'=>'n'
        ]);
    }

    private function itemsFueronModificados($pedido, array $nuevosItems)
    {
        $pedido->loadMissing(['detalles.ingredientes.ingrediente']);
        $detalles = $pedido->detalles ?? collect();

        if ($detalles->count() !== count($nuevosItems)) {
            return true;
        }

        // Construir firmas únicas de los detalles actuales en base de datos
        $existentes = $detalles->map(function ($d) {
            $excluidos = ($d->ingredientes ?? collect())
                ->filter(function ($m) {
                    return $this->normalizarTipoModificacion($m->tipo_modificacion ?? '') === 'Exclusión';
                })
                ->pluck('id_ingrediente')
                ->filter()
                ->sort()
                ->values()
                ->all();

            $agregados = ($d->ingredientes ?? collect())
                ->filter(function ($m) {
                    return $this->normalizarTipoModificacion($m->tipo_modificacion ?? '') === 'Agregado';
                })
                ->pluck('id_ingrediente')
                ->filter()
                ->sort()
                ->values()
                ->all();

            return (int)$d->id_producto . ':' . (int)($d->id_tamaño ?? 0) . ':' . (int)$d->cantidad . ':' . implode(',', $excluidos) . ':' . implode(',', $agregados);
        })->sort()->values()->all();

        // Construir firmas únicas de los items entrantes
        $nuevos = collect($nuevosItems)->map(function ($item) {
            $rawProd = $item['id_producto'] ?? ($item['catalogId'] ?? ($item['id'] ?? 0));
            if (is_string($rawProd) && strpos($rawProd, '_') !== false) {
                $rawProd = explode('_', $rawProd)[0];
            }
            $prodId = (int)$rawProd;

            $tamId = (int)($item['id_tamaño'] ?? ($item['tamano_id'] ?? 0));
            $cant = max(1, (int)($item['cantidad'] ?? ($item['quantity'] ?? 1)));

            $excluidos = [];
            $rawExcl = $item['removedIngredients'] ?? ($item['excluidos'] ?? ($item['excluidosDetails'] ?? []));
            foreach ($rawExcl as $rem) {
                $remId = is_array($rem) ? ($rem['id_ingrediente'] ?? $rem['id'] ?? null) : (is_numeric($rem) ? (int)$rem : null);
                if ($remId) $excluidos[] = (int)$remId;
            }
            sort($excluidos);

            $agregados = [];
            $rawAgre = $item['addedExtras'] ?? ($item['agregados'] ?? ($item['agregadosDetails'] ?? []));
            foreach ($rawAgre as $ext) {
                $extId = is_array($ext) ? ($ext['id_ingrediente'] ?? $ext['id'] ?? null) : (is_numeric($ext) ? (int)$ext : null);
                if ($extId) $agregados[] = (int)$extId;
            }
            sort($agregados);

            return $prodId . ':' . $tamId . ':' . $cant . ':' . implode(',', $excluidos) . ':' . implode(',', $agregados);
        })->sort()->values()->all();

        return $existentes !== $nuevos;
    }

    public function __construct(PedidoRepository $pedidoRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
    }

    public function createPedido($data)
    {
        if (isset($data['total']) && $data['total'] < 0) {
            throw new \InvalidArgumentException('El total del pedido no puede ser negativo.');
        }

        // Crear pedido de forma atómica (siempre nace en estado Pendiente, sin descontar stock)
        $pedido = $this->pedidoRepository->createPedido($data);

        if ($pedido) {
            $comandaNum = $pedido->numero_pedido_dia ?? $pedido->id_pedido;
            $itemsCount = isset($data['items']) && is_array($data['items']) ? count($data['items']) : 1;
            HistorialMovimiento::registrar(
                'pedido',
                'crear',
                'Nuevo pedido ingresado',
                "Comanda #{$comandaNum} (ID #{$pedido->id_pedido})",
                'Cliente: ' . ($pedido->nombre_persona ?? 'Cliente Online') . ' · Total: $' . number_format($pedido->total, 0, ',', '.') . ' · ' . $itemsCount . ' productos',
                $pedido->total
            );
        }

        return $pedido;
    }

    public function descontarInventario($pedidoId)
    {
        return $this->ajustarInventarioPedido($pedidoId, true);
    }

    public function revertirInventario($pedidoId)
    {
        return $this->ajustarInventarioPedido($pedidoId, false);
    }

    /**
     * Aplica descuento (entregado) o reintegro (reversión/cancelación) de ingredientes
     * de forma atómica y limpia la caché pública si cambia la disponibilidad de algún insumo.
     */
    protected function ajustarInventarioPedido($pedidoId, bool $esDescuento): void
    {
        $cambioDisponibilidad = false;

        DB::transaction(function () use ($pedidoId, $esDescuento, &$cambioDisponibilidad) {
            $pedido = Pedido::with(['detalles.ingredientes.ingrediente', 'detalles.producto'])
                ->lockForUpdate()
                ->find($pedidoId);

            if (!$pedido) {
                return;
            }
            if ($esDescuento && $pedido->inventario_descontado) {
                return;
            }
            if (!$esDescuento && !$pedido->inventario_descontado) {
                return;
            }

            foreach ($pedido->detalles as $detalle) {
                $productoId = $detalle->id_producto;
                $tamanoId = $detalle->id_tamaño;
                $cantComprada = max(1, (int)$detalle->cantidad);

                $exclusiones = ($detalle->ingredientes ?? collect())
                    ->filter(fn ($mod) => $this->normalizarTipoModificacion($mod->tipo_modificacion ?? '') === 'Exclusión');

                $excluidosIds = $exclusiones->pluck('id_ingrediente')->filter()->toArray();
                $excluidosNombres = $exclusiones
                    ->map(fn ($mod) => $this->normalizarNombreIngrediente($mod->ingrediente->nombre ?? ''))
                    ->filter()
                    ->toArray();

                $recetaQuery = Producto_ingrediente::where('id_producto', $productoId)
                    ->where('incluido_por_defecto', true);

                if ($tamanoId) {
                    $recetaTamano = (clone $recetaQuery)->where('id_tamaño', $tamanoId)->get();
                    $receta = $recetaTamano->isNotEmpty()
                        ? $recetaTamano
                        : (clone $recetaQuery)->whereNull('id_tamaño')->get();
                } else {
                    $receta = (clone $recetaQuery)->whereNull('id_tamaño')->get();
                }

                if ($receta->isEmpty()) {
                    $receta = Producto_ingrediente::where('id_producto', $productoId)
                        ->where('incluido_por_defecto', true)
                        ->get();
                }

                foreach ($receta as $itemReceta) {
                    $ingrediente = Ingrediente::lockForUpdate()->find($itemReceta->id_ingrediente);
                    if (!$ingrediente || (isset($ingrediente->es_cuantificable) && !$ingrediente->es_cuantificable) || ($ingrediente->tipo_inventario ?? null) === 'no_cuantitativo') {
                        continue;
                    }

                    $nombreNorm = $this->normalizarNombreIngrediente($ingrediente->nombre);
                    if (in_array($itemReceta->id_ingrediente, $excluidosIds) || in_array($nombreNorm, $excluidosNombres)) {
                        continue;
                    }

                    $delta = (float)($itemReceta->cantidad ?? 1) * $cantComprada;
                    $wasAvailable = (bool)$ingrediente->disponible;
                    $ingrediente->cantidad_actual = $esDescuento
                        ? max(0, (float)$ingrediente->cantidad_actual - $delta)
                        : (float)$ingrediente->cantidad_actual + $delta;
                    $ingrediente->disponible = $ingrediente->cantidad_actual > 0;
                    $ingrediente->save();

                    if ($wasAvailable !== (bool)$ingrediente->disponible) {
                        $cambioDisponibilidad = true;
                    }

                    Movimientos::create([
                        'id_ingrediente' => $ingrediente->id_ingrediente,
                        'cantidad' => $delta,
                        'tipo_movimiento' => $esDescuento ? 'Salida' : 'Entrada',
                        'fecha_movimiento' => now()->toDateString(),
                    ]);
                }

                $agregados = ($detalle->ingredientes ?? collect())
                    ->filter(fn ($mod) => $this->normalizarTipoModificacion($mod->tipo_modificacion ?? '') === 'Agregado');

                foreach ($agregados as $itemAgregado) {
                    $ingrediente = $itemAgregado->id_ingrediente
                        ? Ingrediente::lockForUpdate()->find($itemAgregado->id_ingrediente)
                        : null;

                    if (!$ingrediente || (isset($ingrediente->es_cuantificable) && !$ingrediente->es_cuantificable) || ($ingrediente->tipo_inventario ?? null) === 'no_cuantitativo') {
                        continue;
                    }

                    $delta = 1 * $cantComprada;
                    $wasAvailable = (bool)$ingrediente->disponible;
                    $ingrediente->cantidad_actual = $esDescuento
                        ? max(0, (float)$ingrediente->cantidad_actual - $delta)
                        : (float)$ingrediente->cantidad_actual + $delta;
                    $ingrediente->disponible = $ingrediente->cantidad_actual > 0;
                    $ingrediente->save();

                    if ($wasAvailable !== (bool)$ingrediente->disponible) {
                        $cambioDisponibilidad = true;
                    }

                    Movimientos::create([
                        'id_ingrediente' => $ingrediente->id_ingrediente,
                        'cantidad' => $delta,
                        'tipo_movimiento' => $esDescuento ? 'Salida' : 'Entrada',
                        'fecha_movimiento' => now()->toDateString(),
                    ]);
                }
            }

            $pedido->inventario_descontado = $esDescuento;
            $pedido->save();
        });

        if ($cambioDisponibilidad) {
            CatalogCache::invalidate();
        }
    }

    public function getAllPedidos($fecha = null, int $limit = 300)
    {
        return $this->pedidoRepository->getAllPedidos($fecha, $limit);
    }

    public function paginatePedidos($fecha = null, int $perPage = 50, int $page = 1)
    {
        return $this->pedidoRepository->paginatePedidos($fecha, $perPage, $page);
    }

    public function getPedidoById($id)
    {
        return $this->pedidoRepository->getPedidoById($id);
    }

    public function getPedidosByUsuarioId($idUsuario, $fechaInicio = null, $fechaFin = null, $limit = 50)
    {
        return $this->pedidoRepository->getPedidosByUsuarioId($idUsuario, $fechaInicio, $fechaFin, $limit);
    }

    public function getPedidosByEstadoId($idEstado)
    {
        return $this->pedidoRepository->getPedidosByEstadoId($idEstado);
    }

    public function getPedidosByEstadoPago($estadoPago)
    {
        return $this->pedidoRepository->getPedidosByEstadoPago($estadoPago);
    }

    public function getPedidoByComandaTurno($numeroComanda, $targetDate = null)
    {
        return $this->pedidoRepository->getPedidoByComandaTurno($numeroComanda, $targetDate);
    }

    public function getVentaByPedidoId($id)
    {
        return $this->pedidoRepository->getVentaByPedidoId($id);
    }

    public function updatePedido($id, $data)
    {
        if (isset($data['total']) && $data['total'] < 0) {
            throw new \InvalidArgumentException('El total del pedido no puede ser negativo.');
        }

        $pedidoAnterior = Pedido::find($id) ?? $this->pedidoRepository->getPedidoBySearch($id);

        if ($pedidoAnterior) {
            $estadosValidos = [1, 2, 3, 4, 5];
            if (isset($data['id_estado_pedido']) && !in_array((int)$data['id_estado_pedido'], $estadosValidos, true)) {
                throw new \InvalidArgumentException('El estado de pedido especificado no es válido.');
            }

            $estadoActual = (int)$pedidoAnterior->id_estado_pedido;
            $nuevoEstadoInput = isset($data['id_estado_pedido']) ? (int)$data['id_estado_pedido'] : null;

            // Regla 1: Un pedido Entregado (4) no se puede Cancelar (5)
            if ($estadoActual === 4 && $nuevoEstadoInput === 5) {
                throw new \InvalidArgumentException('Un pedido que ya ha sido entregado no puede ser cancelado.');
            }

            // Regla 2: Un pedido Cancelado (5) no puede reabrirse ni cambiar a otro estado operativo
            if ($estadoActual === 5 && $nuevoEstadoInput !== null && $nuevoEstadoInput !== 5) {
                throw new \InvalidArgumentException('Un pedido cancelado no puede reabrirse a otro estado.');
            }

            // Regla 3: Un pedido Cancelado (5) no se puede marcar como Pagado (2)
            if ($estadoActual === 5 && isset($data['id_estado_pago']) && (int)$data['id_estado_pago'] === 2) {
                throw new \InvalidArgumentException('Un pedido cancelado no puede ser marcado como pagado.');
            }
        }

        // Verificar si los items realmente cambiaron respecto a la base de datos
        $itemsRealmenteCambiaron = false;
        if (isset($data['items']) && is_array($data['items']) && $pedidoAnterior) {
            $itemsRealmenteCambiaron = $this->itemsFueronModificados($pedidoAnterior, $data['items']);
            if (!$itemsRealmenteCambiaron) {
                // Si los items son idénticos a los ya guardados, no borrar ni recrear detalles
                unset($data['items']);
            }
        }

        // Si se modifican los items de forma efectiva y ya se había descontado stock, revertir el anterior primero
        if ($itemsRealmenteCambiaron && $pedidoAnterior && $pedidoAnterior->inventario_descontado) {
            $this->revertirInventario($pedidoAnterior->id_pedido);
        }

        $nuevoPedido = $this->pedidoRepository->updatePedido($id, $data);
        $nuevoEstado = isset($data['id_estado_pedido']) 
            ? (int)$data['id_estado_pedido'] 
            : ($nuevoPedido ? (int)$nuevoPedido->id_estado_pedido : 1);

        $targetId = $nuevoPedido ? $nuevoPedido->id_pedido : $id;

        // Descontar inventario ÚNICAMENTE si el pedido llega al estado Entregado (4) y no ha sido descontado aún
        if ($nuevoPedido && $nuevoEstado === 4 && !$nuevoPedido->inventario_descontado) {
            $this->descontarInventario($targetId);
        }

        // Si el pedido ya tenía inventario descontado y cambia a otro estado distinto de Entregado (ej: Cancelado 5, o devuelto a preparación)
        if ($nuevoPedido && $nuevoEstado !== 4 && $nuevoPedido->inventario_descontado) {
            $this->revertirInventario($targetId);
        }

        // =========================================================================
        // AUDITORÍA AUTOMÁTICA DE MOVIMIENTOS Y TRAZABILIDAD
        // =========================================================================
        if ($nuevoPedido && $pedidoAnterior) {
            $comandaNum = $nuevoPedido->numero_pedido_dia ?? $nuevoPedido->id_pedido;
            $entidad = "Comanda #{$comandaNum} (ID #{$nuevoPedido->id_pedido})";
            $cliente = $nuevoPedido->nombre_persona ?? 'Cliente';

            // 1. Cambio de Estado
            if (isset($data['id_estado_pedido']) && (int)$data['id_estado_pedido'] !== (int)$pedidoAnterior->id_estado_pedido) {
                $estadoId = (int)$data['id_estado_pedido'];
                switch ($estadoId) {
                    case 1:
                        HistorialMovimiento::registrar(
                            'pedido',
                            'estado',
                            'Pedido marcado como Pendiente',
                            $entidad,
                            "Comanda devuelta a estado Pendiente. Cliente: {$cliente}",
                            $nuevoPedido->total
                        );
                        break;
                    case 2:
                        HistorialMovimiento::registrar(
                            'pedido',
                            'estado',
                            'Pedido en preparación',
                            $entidad,
                            "Comanda ingresó a preparación en cocina. Cliente: {$cliente}",
                            $nuevoPedido->total
                        );
                        break;
                    case 3:
                        HistorialMovimiento::registrar(
                            'pedido',
                            'estado',
                            'Pedido listo para retiro/entrega',
                            $entidad,
                            "Comanda lista en mesón de despacho. Cliente: {$cliente}",
                            $nuevoPedido->total
                        );
                        break;
                    case 4:
                        HistorialMovimiento::registrar(
                            'pedido',
                            'entregado',
                            'Pedido ENTREGADO al cliente',
                            $entidad,
                            "Entregado exitosamente a {$cliente} el " . now()->format('d/m/Y') . ' a las ' . now()->format('H:i:s') . ' hrs. Inventario descontado.',
                            $nuevoPedido->total
                        );
                        break;
                    case 5:
                        HistorialMovimiento::registrar(
                            'pedido',
                            'cancelar',
                            'Pedido CANCELADO',
                            $entidad,
                            "Comanda de {$cliente} fue cancelada. Reversión de stock procesada si aplicaba.",
                            $nuevoPedido->total
                        );
                        break;
                    default:
                        $estadoNombre = $nuevoPedido->estado_pedido?->nombre ?? "Estado #{$estadoId}";
                        HistorialMovimiento::registrar(
                            'pedido',
                            'estado',
                            "Estado actualizado a {$estadoNombre}",
                            $entidad,
                            "Comanda de {$cliente} cambió de estado a {$estadoNombre}.",
                            $nuevoPedido->total
                        );
                        break;
                }
            }

            // 2. Cambio de Estado de Pago (Marcado como Pagado)
            if (isset($data['id_estado_pago']) && (int)$data['id_estado_pago'] === 2 && (int)$pedidoAnterior->id_estado_pago !== 2) {
                HistorialMovimiento::registrar(
                    'pedido',
                    'pago',
                    'Pago confirmado y registrado',
                    $entidad,
                    "Cobro por $" . number_format($nuevoPedido->total, 0, ',', '.') . " recibido vía " . ($nuevoPedido->metodo_pago ?? 'Efectivo') . " para {$cliente}.",
                    $nuevoPedido->total
                );
            }

            // 3. Modificación de Productos o Ajustes en el Pedido
            if (isset($data['items']) && is_array($data['items'])) {
                $cambioTotal = abs((float)($nuevoPedido->total ?? 0) - (float)($pedidoAnterior->total ?? 0)) > 0.01;
                $cambioNotas = isset($data['notas']) && $data['notas'] !== $pedidoAnterior->notas;
                if ($cambioTotal || $cambioNotas) {
                    HistorialMovimiento::registrar(
                        'pedido',
                        'editar',
                        'Productos o ingredientes modificados',
                        $entidad,
                        "Ajuste en la comanda de {$cliente}. Total actualizado a $" . number_format($nuevoPedido->total, 0, ',', '.'),
                        $nuevoPedido->total
                    );
                }
            }
        }

        return $this->pedidoRepository->getPedidoById($targetId) ?? $nuevoPedido;
    }

    public function deletePedidoById($id)
    {
        HistorialMovimiento::registrar(
            'pedido',
            'eliminar',
            'Pedido eliminado',
            "Pedido ID #{$id}",
            'Eliminación manual del pedido desde la base de datos'
        );
        return $this->pedidoRepository->deletePedidoById($id);
    }
}