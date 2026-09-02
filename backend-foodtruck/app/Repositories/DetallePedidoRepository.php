<?php

namespace App\Repositories;

use App\Models\Detalle_pedido;

class DetallePedidoRepository
{
    public function getAllDetallePedidos()
    {
        return Detalle_pedido::with(['producto', 'tamano', 'ingredientes.ingrediente'])->get();
    }

    public function getDetallePedidoById($id)
    {
        return Detalle_pedido::with(['producto', 'tamano', 'ingredientes.ingrediente'])->find($id);
    }

    public function createDetallePedido($data)
    {
        return Detalle_pedido::create($data);
    }

    public function getDetallePedidosByPedidoId($id_pedido)
    {
        return Detalle_pedido::with(['producto', 'tamano', 'ingredientes.ingrediente'])->where('id_pedido', $id_pedido)->get();
    }

    public function updateDetallePedido($id, $data)
    {
        $detallePedido = Detalle_pedido::find($id);
        if ($detallePedido) {
            $detallePedido->update($data);
            return $detallePedido;
        }
        return null;
    }

    public function deleteDetallePedidoById($id)
    {
        $detallePedido = Detalle_pedido::find($id);
        if ($detallePedido) {
            $detallePedido->delete();
            return true;
        }
        return false;
    }
}