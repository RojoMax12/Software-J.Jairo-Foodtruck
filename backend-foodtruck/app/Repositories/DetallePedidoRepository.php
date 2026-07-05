<?php

namespace App\Repositories;
use App\Models\Detalle_Pedido;

class DetallePedidoRepository
{
    public function createDetallePedido($data)
    {
        return Detalle_Pedido::create($data);
    }

    public function getDetallePedidosByPedidoId($id_pedido)
    {
        return Detalle_Pedido::where('id_pedido', $id_pedido)->get();
    }

    public function updateDetallePedido($id, $data)
    {
        $detallePedido = Detalle_Pedido::find($id);
        if ($detallePedido) {
            $detallePedido->update($data);
            return $detallePedido;
        }
        return null;
    }

    public function deleteDetallePedidoById($id)
    {
        $detallePedido = Detalle_Pedido::find($id);
        if ($detallePedido) {
            $detallePedido->delete();
            return true;
        }
        return false;
    }

}