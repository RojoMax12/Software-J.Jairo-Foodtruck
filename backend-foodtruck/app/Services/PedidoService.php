<?php

namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Models\Ingrediente;
use App\Models\Movimientos;
use App\Models\Pedido;
use App\Models\Producto_ingrediente;
use App\Repositories\PedidoRepository;

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
        $pedido = Pedido::with(['detalles.ingredientes.ingrediente', 'detalles.producto'])->find($pedidoId);
        if (!$pedido || $pedido->inventario_descontado) {
            return;
        }

        foreach ($pedido->detalles as $detalle) {
            $productoId = $detalle->id_producto;
            $tamanoId = $detalle->id_tamaño;
            $cantComprada = max(1, (int)$detalle->cantidad);

            // Obtener exclusiones de este detalle (por ID y por nombre)
            $exclusiones = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = $this->normalizarTipoModificacion($mod->tipo_modificacion ?? '');
                    return $tipo === 'Exclusión';
                });

            $excluidosIds = $exclusiones->pluck('id_ingrediente')->filter()->toArray();
            $excluidosNombres = $exclusiones->map(function ($mod) {
                return $this->normalizarNombreIngrediente($mod->ingrediente->nombre ?? '');
            })->filter()->toArray();

            // Receta base: ingredientes incluidos por defecto para el producto y tamaño
            $recetaQuery = Producto_ingrediente::where('id_producto', $productoId)
                ->where('incluido_por_defecto', true);

            if ($tamanoId) {
                $recetaTamano = (clone $recetaQuery)->where('id_tamaño', $tamanoId)->get();
                if ($recetaTamano->isNotEmpty()) {
                    $receta = $recetaTamano;
                } else {
                    $receta = (clone $recetaQuery)->whereNull('id_tamaño')->get();
                }
            } else {
                $receta = (clone $recetaQuery)->whereNull('id_tamaño')->get();
            }

            // Fallback: si no encontró con los filtros anteriores, traer cualquier receta base del producto
            if ($receta->isEmpty()) {
                $receta = Producto_ingrediente::where('id_producto', $productoId)
                    ->where('incluido_por_defecto', true)
                    ->get();
            }

            // 1. Descontar ingredientes de la receta base (omitiendo los excluidos)
            foreach ($receta as $itemReceta) {
                $ingrediente = Ingrediente::find($itemReceta->id_ingrediente);
                if (!$ingrediente) {
                    continue;
                }

                $nombreNorm = $this->normalizarNombreIngrediente($ingrediente->nombre);

                // Si el ingrediente fue excluido por el cliente, NO se descuenta
                if (in_array($itemReceta->id_ingrediente, $excluidosIds) || in_array($nombreNorm, $excluidosNombres)) {
                    continue;
                }

                $cantReceta = (float)($itemReceta->cantidad ?? 1);
                $descuentoTotal = $cantReceta * $cantComprada;

                $ingrediente->cantidad_actual = max(0, (float)$ingrediente->cantidad_actual - $descuentoTotal);
                if ($ingrediente->cantidad_actual <= 0) {
                    $ingrediente->disponible = false;
                }
                $ingrediente->save();

                // Registrar movimiento de salida
                Movimientos::create([
                    'id_ingrediente' => $ingrediente->id_ingrediente,
                    'cantidad' => $descuentoTotal,
                    'tipo_movimiento' => 'Salida',
                    'fecha_movimiento' => now()->toDateString(),
                ]);
            }

            // 2. Descontar agregados / extras personalizados
            $agregados = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = $this->normalizarTipoModificacion($mod->tipo_modificacion ?? '');
                    return $tipo === 'Agregado';
                });

            foreach ($agregados as $itemAgregado) {
                $ingrediente = $itemAgregado->id_ingrediente 
                    ? Ingrediente::find($itemAgregado->id_ingrediente)
                    : null;

                if ($ingrediente) {
                    $cantExtra = 1; // 1 porción extra por producto
                    $descuentoTotal = $cantExtra * $cantComprada;

                    $ingrediente->cantidad_actual = max(0, (float)$ingrediente->cantidad_actual - $descuentoTotal);
                    if ($ingrediente->cantidad_actual <= 0) {
                        $ingrediente->disponible = false;
                    }
                    $ingrediente->save();

                    Movimientos::create([
                        'id_ingrediente' => $ingrediente->id_ingrediente,
                        'cantidad' => $descuentoTotal,
                        'tipo_movimiento' => 'Salida',
                        'fecha_movimiento' => now()->toDateString(),
                    ]);
                }
            }
        }

        $pedido->inventario_descontado = true;
        $pedido->save();
    }

    public function revertirInventario($pedidoId)
    {
        $pedido = Pedido::with(['detalles.ingredientes.ingrediente', 'detalles.producto'])->find($pedidoId);
        if (!$pedido || !$pedido->inventario_descontado) {
            return;
        }

        foreach ($pedido->detalles as $detalle) {
            $productoId = $detalle->id_producto;
            $tamanoId = $detalle->id_tamaño;
            $cantComprada = max(1, (int)$detalle->cantidad);

            $exclusiones = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = $this->normalizarTipoModificacion($mod->tipo_modificacion ?? '');
                    return $tipo === 'Exclusión';
                });

            $excluidosIds = $exclusiones->pluck('id_ingrediente')->filter()->toArray();
            $excluidosNombres = $exclusiones->map(function ($mod) {
                return $this->normalizarNombreIngrediente($mod->ingrediente->nombre ?? '');
            })->filter()->toArray();

            $recetaQuery = Producto_ingrediente::where('id_producto', $productoId)
                ->where('incluido_por_defecto', true);

            if ($tamanoId) {
                $recetaTamano = (clone $recetaQuery)->where('id_tamaño', $tamanoId)->get();
                if ($recetaTamano->isNotEmpty()) {
                    $receta = $recetaTamano;
                } else {
                    $receta = (clone $recetaQuery)->whereNull('id_tamaño')->get();
                }
            } else {
                $receta = (clone $recetaQuery)->whereNull('id_tamaño')->get();
            }

            if ($receta->isEmpty()) {
                $receta = Producto_ingrediente::where('id_producto', $productoId)
                    ->where('incluido_por_defecto', true)
                    ->get();
            }

            // 1. Revertir receta base no excluida
            foreach ($receta as $itemReceta) {
                $ingrediente = Ingrediente::find($itemReceta->id_ingrediente);
                if (!$ingrediente) {
                    continue;
                }

                $nombreNorm = $this->normalizarNombreIngrediente($ingrediente->nombre);

                if (in_array($itemReceta->id_ingrediente, $excluidosIds) || in_array($nombreNorm, $excluidosNombres)) {
                    continue;
                }

                $cantReceta = (float)($itemReceta->cantidad ?? 1);
                $reversionTotal = $cantReceta * $cantComprada;

                $ingrediente->cantidad_actual = (float)$ingrediente->cantidad_actual + $reversionTotal;
                if ($ingrediente->cantidad_actual > 0) {
                    $ingrediente->disponible = true;
                }
                $ingrediente->save();

                Movimientos::create([
                    'id_ingrediente' => $ingrediente->id_ingrediente,
                    'cantidad' => $reversionTotal,
                    'tipo_movimiento' => 'Entrada',
                    'fecha_movimiento' => now()->toDateString(),
                ]);
            }

            // 2. Revertir agregados / extras
            $agregados = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = $this->normalizarTipoModificacion($mod->tipo_modificacion ?? '');
                    return $tipo === 'Agregado';
                });

            foreach ($agregados as $itemAgregado) {
                $ingrediente = $itemAgregado->id_ingrediente 
                    ? Ingrediente::find($itemAgregado->id_ingrediente)
                    : null;

                if ($ingrediente) {
                    $cantExtra = 1;
                    $reversionTotal = $cantExtra * $cantComprada;

                    $ingrediente->cantidad_actual = (float)$ingrediente->cantidad_actual + $reversionTotal;
                    if ($ingrediente->cantidad_actual > 0) {
                        $ingrediente->disponible = true;
                    }
                    $ingrediente->save();

                    Movimientos::create([
                        'id_ingrediente' => $ingrediente->id_ingrediente,
                        'cantidad' => $reversionTotal,
                        'tipo_movimiento' => 'Entrada',
                        'fecha_movimiento' => now()->toDateString(),
                    ]);
                }
            }
        }

        $pedido->inventario_descontado = false;
        $pedido->save();
    }

    public function getAllPedidos($fecha = null)
    {
        return $this->pedidoRepository->getAllPedidos($fecha);
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
            // Regla 1: Un pedido Entregado (4) no se puede Cancelar (5)
            if ((int)$pedidoAnterior->id_estado_pedido === 4 && isset($data['id_estado_pedido']) && (int)$data['id_estado_pedido'] === 5) {
                throw new \InvalidArgumentException('Un pedido que ya ha sido entregado no puede ser cancelado.');
            }

            // Regla 2: Un pedido Cancelado (5) no se puede marcar como Pagado (2)
            if ((int)$pedidoAnterior->id_estado_pedido === 5 && isset($data['id_estado_pago']) && (int)$data['id_estado_pago'] === 2) {
                throw new \InvalidArgumentException('Un pedido cancelado no puede ser marcado como pagado.');
            }
        }

        // Si se modifican los items y ya se había descontado stock, revertir el anterior primero
        if (isset($data['items']) && is_array($data['items']) && $pedidoAnterior && $pedidoAnterior->inventario_descontado) {
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