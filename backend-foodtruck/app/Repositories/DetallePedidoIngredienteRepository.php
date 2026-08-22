<?php

namespace App\Repositories;
use App\Models\Detalle_pedido_ingrediente;

class DetallePedidoIngredienteRepository
{
    public function createDetallePedidoIngrediente($data)
    {
        return Detalle_pedido_ingrediente::create($data);
    }

    public function getDetallePedidoIngredientesByDetallePedidoId($id_detalle_pedido)
    {
        return Detalle_pedido_ingrediente::where('id_detalle_pedido', $id_detalle_pedido)->get();
    }

    public function updateDetallePedidoIngrediente($id, $data)
    {
        $detallePedidoIngrediente = Detalle_pedido_ingrediente::find($id);
        if ($detallePedidoIngrediente) {
            $detallePedidoIngrediente->update($data);
            return $detallePedidoIngrediente;
        }
        return null;
    }

    public function deleteDetallePedidoIngredienteById($id)
    {
        $detallePedidoIngrediente = Detalle_pedido_ingrediente::find($id);
        if ($detallePedidoIngrediente) {
            $detallePedidoIngrediente->delete();
            return true;
        }
        return false;
    }
}