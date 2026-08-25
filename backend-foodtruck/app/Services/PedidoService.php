<?php

namespace App\Services;

use App\Models\Ingrediente;
use App\Models\Movimientos;
use App\Models\Pedido;
use App\Models\Producto_ingrediente;
use App\Repositories\PedidoRepository;

# Servicio Pedido
class PedidoService
{
    protected $pedidoRepository;

    public function __construct(PedidoRepository $pedidoRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
    }

    public function createPedido($data)
    {
        if (isset($data['total']) && $data['total'] < 0) {
            throw new \InvalidArgumentException('El total del pedido no puede ser negativo.');
        }

        $pedido = $this->pedidoRepository->createPedido($data);

        // Descontar inventario al crear el pedido
        if ($pedido && $pedido->id_pedido) {
            $this->descontarInventario($pedido->id_pedido);
        }

        return $pedido;
    }

    public function descontarInventario($pedidoId)
    {
        $pedido = Pedido::with(['detalles.ingredientes'])->find($pedidoId);
        if (!$pedido || $pedido->inventario_descontado) {
            return;
        }

        foreach ($pedido->detalles as $detalle) {
            $productoId = $detalle->id_producto;
            $tamanoId = $detalle->id_tamaño;
            $cantComprada = $detalle->cantidad;

            // Obtener exclusiones de este detalle
            $excluidosIds = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = strtolower($mod->tipo_modificacion ?? '');
                    return str_contains($tipo, 'exclu') || str_contains($tipo, 'quit');
                })
                ->pluck('id_ingrediente')
                ->filter()
                ->toArray();

            // Receta: ingredientes por tamaño o globales para el producto
            $receta = Producto_ingrediente::where('id_producto', $productoId)
                ->where(function ($query) use ($tamanoId) {
                    $query->where('id_tamaño', $tamanoId)
                        ->orWhereNull('id_tamaño');
                })
                ->where('incluido_por_defecto', true)
                ->get();

            foreach ($receta as $itemReceta) {
                // Si el ingrediente fue excluido, no se descuenta
                if (in_array($itemReceta->id_ingrediente, $excluidosIds)) {
                    continue;
                }

                $ingrediente = Ingrediente::find($itemReceta->id_ingrediente);
                if ($ingrediente) {
                    $descuentoTotal = $itemReceta->cantidad * $cantComprada;
                    $ingrediente->cantidad_actual = max(0, $ingrediente->cantidad_actual - $descuentoTotal);
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
            }

            // Descontar agregados / extras
            $agregados = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = strtolower($mod->tipo_modificacion ?? '');
                    return str_contains($tipo, 'agre') || str_contains($tipo, 'extra');
                });

            foreach ($agregados as $itemAgregado) {
                $ingrediente = Ingrediente::find($itemAgregado->id_ingrediente);
                if ($ingrediente) {
                    $descuentoTotal = 1 * $cantComprada;
                    $ingrediente->cantidad_actual = max(0, $ingrediente->cantidad_actual - $descuentoTotal);
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
        $pedido = Pedido::with(['detalles.ingredientes'])->find($pedidoId);
        if (!$pedido || !$pedido->inventario_descontado) {
            return;
        }

        foreach ($pedido->detalles as $detalle) {
            $productoId = $detalle->id_producto;
            $tamanoId = $detalle->id_tamaño;
            $cantComprada = $detalle->cantidad;

            $excluidosIds = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = strtolower($mod->tipo_modificacion ?? '');
                    return str_contains($tipo, 'exclu') || str_contains($tipo, 'quit');
                })
                ->pluck('id_ingrediente')
                ->filter()
                ->toArray();

            $receta = Producto_ingrediente::where('id_producto', $productoId)
                ->where(function ($query) use ($tamanoId) {
                    $query->where('id_tamaño', $tamanoId)
                        ->orWhereNull('id_tamaño');
                })
                ->where('incluido_por_defecto', true)
                ->get();

            foreach ($receta as $itemReceta) {
                if (in_array($itemReceta->id_ingrediente, $excluidosIds)) {
                    continue;
                }

                $ingrediente = Ingrediente::find($itemReceta->id_ingrediente);
                if ($ingrediente) {
                    $reversionTotal = $itemReceta->cantidad * $cantComprada;
                    $ingrediente->cantidad_actual = $ingrediente->cantidad_actual + $reversionTotal;
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

            $agregados = ($detalle->ingredientes ?? collect())
                ->filter(function ($mod) {
                    $tipo = strtolower($mod->tipo_modificacion ?? '');
                    return str_contains($tipo, 'agre') || str_contains($tipo, 'extra');
                });

            foreach ($agregados as $itemAgregado) {
                $ingrediente = Ingrediente::find($itemAgregado->id_ingrediente);
                if ($ingrediente) {
                    $reversionTotal = 1 * $cantComprada;
                    $ingrediente->cantidad_actual = $ingrediente->cantidad_actual + $reversionTotal;
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

    public function getAllPedidos()
    {
        return $this->pedidoRepository->getAllPedidos();
    }

    public function getPedidoById($id)
    {
        return $this->pedidoRepository->getPedidoById($id);
    }

    public function getPedidosByUsuarioId($idUsuario)
    {
        return $this->pedidoRepository->getPedidosByUsuarioId($idUsuario);
    }

    public function getPedidosByEstadoId($idEstado)
    {
        return $this->pedidoRepository->getPedidosByEstadoId($idEstado);
    }

    public function getPedidosByEstadoPago($estadoPago)
    {
        return $this->pedidoRepository->getPedidosByEstadoPago($estadoPago);
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

        $pedidoAnterior = $this->getPedidoById($id);
        $nuevoPedido = $this->pedidoRepository->updatePedido($id, $data);

        $nuevoEstado = isset($data['id_estado_pedido']) ? (int)$data['id_estado_pedido'] : null;

        // Descontar inventario 1 sola vez si avanza a En preparación (2), Listo (3) o Entregado (4)
        if ($nuevoPedido && in_array($nuevoEstado, [2, 3, 4])) {
            $this->descontarInventario($id);
        }

        // Revertir inventario si el pedido es Cancelado (5)
        if ($nuevoPedido && $nuevoEstado === 5) {
            $this->revertirInventario($id);
        }

        return $nuevoPedido;
    }

    public function deletePedidoById($id)
    {
        return $this->pedidoRepository->deletePedidoById($id);
    }
}